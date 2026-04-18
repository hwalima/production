<?php

namespace Tests\Feature;

use App\Models\Consumable;
use App\Models\DailyProduction;
use App\Models\LabourEnergy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────

    private function writer(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function viewer(): User
    {
        return User::factory()->create(['role' => 'viewer']);
    }

    private function csvFile(string $content, string $name = 'test.csv'): UploadedFile
    {
        // Give temp file a .csv extension so extension-based validation passes
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('imp_') . '.csv';
        file_put_contents($path, $content);
        return new UploadedFile($path, $name, 'text/csv', UPLOAD_ERR_OK, true);
    }

    // ── Access control ────────────────────────────────────────────────────

    /** @test */
    public function import_hub_requires_auth(): void
    {
        $this->get(route('import.index'))->assertRedirect(route('login'));
    }

    /** @test */
    public function import_hub_requires_write_role(): void
    {
        $this->actingAs($this->viewer())
            ->get(route('import.index'))
            ->assertForbidden();
    }

    /** @test */
    public function import_hub_is_accessible_to_admin(): void
    {
        $this->actingAs($this->writer())
            ->get(route('import.index'))
            ->assertOk();
    }

    // ── Template downloads ────────────────────────────────────────────────

    /** @test */
    public function production_template_download_returns_csv(): void
    {
        $response = $this->actingAs($this->writer())
            ->get(route('import.template', 'production'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('date', $response->getContent());
        $this->assertStringContainsString('gold_smelted', $response->getContent());
    }

    /** @test */
    public function consumables_template_download_returns_csv(): void
    {
        $response = $this->actingAs($this->writer())
            ->get(route('import.template', 'consumables'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('name', $response->getContent());
        $this->assertStringContainsString('category', $response->getContent());
    }

    /** @test */
    public function labour_energy_template_download_returns_csv(): void
    {
        $response = $this->actingAs($this->writer())
            ->get(route('import.template', 'labour-energy'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('zesa_cost', $response->getContent());
    }

    /** @test */
    public function unknown_template_type_returns_404(): void
    {
        $this->actingAs($this->writer())
            ->get(route('import.template', 'nonexistent'))
            ->assertNotFound();
    }

    // ── Production import ─────────────────────────────────────────────────

    /** @test */
    public function can_import_production_records_from_csv(): void
    {
        $csv = implode("\r\n", [
            'date,shift,mining_site,ore_hoisted,ore_hoisted_target,waste_hoisted,uncrushed_stockpile,ore_crushed,unmilled_stockpile,ore_milled,ore_milled_target,gold_smelted,purity_percentage,fidelity_price',
            '2026-04-01,Day,Main Pit,100.00,110.00,50.00,5.00,95.00,3.00,92.00,95.00,45.50,92.00,3450000.00',
            '2026-04-02,Night,Main Pit,90.00,,45.00,,88.00,,85.00,,42.00,91.00,3400000.00',
        ]);

        $this->actingAs($this->writer())
            ->post(route('import.production'), ['file' => $this->csvFile($csv)])
            ->assertRedirect(route('import.production'));

        $this->assertEquals(2, DailyProduction::count());
        $this->assertDatabaseHas('daily_productions', [
            'shift'        => 'Day',
            'gold_smelted' => 45.50,
        ]);
    }

    /** @test */
    public function production_import_upserts_on_date_and_shift(): void
    {
        DailyProduction::create([
            'date'                => '2026-04-01',
            'shift'               => 'Day',
            'ore_hoisted'         => 50.00,
            'waste_hoisted'       => 10.00,
            'uncrushed_stockpile' => 0.00,
            'ore_crushed'         => 48.00,
            'unmilled_stockpile'  => 0.00,
            'ore_milled'          => 45.00,
            'gold_smelted'        => 20.00,
            'purity_percentage'   => 90.00,
            'fidelity_price'      => 3000000.00,
        ]);

        $csv = implode("\r\n", [
            'date,shift,ore_hoisted,waste_hoisted,ore_crushed,ore_milled,gold_smelted,purity_percentage,fidelity_price',
            '2026-04-01,Day,100.00,50.00,95.00,92.00,45.50,92.00,3450000.00',
        ]);

        $this->actingAs($this->writer())
            ->post(route('import.production'), ['file' => $this->csvFile($csv)]);

        $this->assertEquals(1, DailyProduction::count());
        $this->assertDatabaseHas('daily_productions', ['shift' => 'Day', 'gold_smelted' => 45.50]);
    }

    /** @test */
    public function production_import_skips_rows_with_invalid_date(): void
    {
        $csv = implode("\r\n", [
            'date,ore_hoisted,waste_hoisted,ore_crushed,ore_milled,gold_smelted,purity_percentage,fidelity_price',
            'not-a-date,100.00,50.00,95.00,92.00,45.50,92.00,3450000.00',
        ]);

        $this->actingAs($this->writer())
            ->post(route('import.production'), ['file' => $this->csvFile($csv)])
            ->assertRedirect(route('import.production'))
            ->assertSessionHas('import_result');

        $result = session('import_result');
        $this->assertEquals(0, $result['inserted']);
        $this->assertCount(1, $result['errors']);
    }

    /** @test */
    public function production_import_rejects_missing_required_columns(): void
    {
        $csv = "date,ore_hoisted\r\n2026-04-01,100.00";

        $this->actingAs($this->writer())
            ->post(route('import.production'), ['file' => $this->csvFile($csv)])
            ->assertRedirect()
            ->assertSessionHasErrors('file');
    }

    /** @test */
    public function production_import_skips_blank_rows(): void
    {
        $csv = implode("\r\n", [
            'date,ore_hoisted,waste_hoisted,ore_crushed,ore_milled,gold_smelted,purity_percentage,fidelity_price',
            '2026-04-01,100.00,50.00,95.00,92.00,45.50,92.00,3450000.00',
            ',,,,,,,',
            '2026-04-02,90.00,45.00,88.00,85.00,42.00,91.00,3400000.00',
        ]);

        $this->actingAs($this->writer())
            ->post(route('import.production'), ['file' => $this->csvFile($csv)]);

        $this->assertEquals(2, DailyProduction::count());
    }

    // ── Consumables import ────────────────────────────────────────────────

    /** @test */
    public function can_import_consumables_from_csv(): void
    {
        $csv = implode("\r\n", [
            'name,category,description,purchase_unit,use_unit,units_per_pack,pack_cost,reorder_level',
            'Drill Bit 38mm,mechanical,Standard rock drill bit,box,each,12,1200.00,24',
            'Lime,chemicals,pH adjustment,bag,kg,25,450.00,50',
        ]);

        $this->actingAs($this->writer())
            ->post(route('import.consumables'), ['file' => $this->csvFile($csv)])
            ->assertRedirect(route('import.consumables'));

        $this->assertEquals(2, Consumable::count());
        $this->assertDatabaseHas('consumables', ['name' => 'Drill Bit 38mm', 'category' => 'mechanical']);
    }

    /** @test */
    public function consumables_import_upserts_on_name(): void
    {
        Consumable::create([
            'name'           => 'Drill Bit 38mm',
            'category'       => 'mechanical',
            'purchase_unit'  => 'box',
            'use_unit'       => 'each',
            'units_per_pack' => 12,
            'pack_cost'      => 1000.00,
            'reorder_level'  => 24,
        ]);

        $csv = implode("\r\n", [
            'name,category,purchase_unit,use_unit,units_per_pack,pack_cost,reorder_level',
            'Drill Bit 38mm,mechanical,box,each,12,1400.00,24',
        ]);

        $this->actingAs($this->writer())
            ->post(route('import.consumables'), ['file' => $this->csvFile($csv)]);

        $this->assertEquals(1, Consumable::count());
        $this->assertDatabaseHas('consumables', ['name' => 'Drill Bit 38mm', 'pack_cost' => 1400.00]);
    }

    /** @test */
    public function consumables_import_rejects_invalid_category(): void
    {
        $csv = implode("\r\n", [
            'name,category,purchase_unit,use_unit,units_per_pack,pack_cost',
            'Widget,invalid_cat,box,each,10,500.00',
        ]);

        $this->actingAs($this->writer())
            ->post(route('import.consumables'), ['file' => $this->csvFile($csv)])
            ->assertRedirect()
            ->assertSessionHas('import_result');

        $result = session('import_result');
        $this->assertEquals(0, $result['inserted']);
        $this->assertCount(1, $result['errors']);
    }

    // ── Labour / Energy import ────────────────────────────────────────────

    /** @test */
    public function can_import_labour_energy_from_csv(): void
    {
        $csv = implode("\r\n", [
            'date,zesa_cost,diesel_cost,labour_cost',
            '2026-04-01,15000.00,22000.00,85000.00',
            '2026-04-02,14500.00,21000.00,83000.00',
        ]);

        $this->actingAs($this->writer())
            ->post(route('import.labour-energy'), ['file' => $this->csvFile($csv)])
            ->assertRedirect(route('import.labour-energy'));

        $this->assertEquals(2, LabourEnergy::count());
        $this->assertDatabaseHas('labour_energy', ['zesa_cost' => 15000.00, 'diesel_cost' => 22000.00]);
    }

    /** @test */
    public function labour_energy_import_upserts_on_date(): void
    {
        LabourEnergy::create([
            'date'        => '2026-04-01',
            'zesa_cost'   => 10000.00,
            'diesel_cost' => 10000.00,
            'labour_cost' => 50000.00,
        ]);

        $csv = implode("\r\n", [
            'date,zesa_cost,diesel_cost,labour_cost',
            '2026-04-01,15000.00,22000.00,85000.00',
        ]);

        $this->actingAs($this->writer())
            ->post(route('import.labour-energy'), ['file' => $this->csvFile($csv)]);

        $this->assertEquals(1, LabourEnergy::count());
        // Confirm the zesa_cost was updated (not the original 10000)
        $this->assertDatabaseHas('labour_energy', ['zesa_cost' => 15000.00, 'diesel_cost' => 22000.00]);
    }

    /** @test */
    public function labour_energy_import_skips_rows_with_invalid_date(): void
    {
        $csv = implode("\r\n", [
            'date,zesa_cost,diesel_cost,labour_cost',
            'bad-date,15000.00,22000.00,85000.00',
        ]);

        $this->actingAs($this->writer())
            ->post(route('import.labour-energy'), ['file' => $this->csvFile($csv)])
            ->assertRedirect()
            ->assertSessionHas('import_result');

        $result = session('import_result');
        $this->assertEquals(0, $result['inserted']);
        $this->assertCount(1, $result['errors']);
    }

    /** @test */
    public function import_rejects_non_csv_file_types(): void
    {
        $txt = UploadedFile::fake()->create('data.txt', 1, 'text/plain');

        $this->actingAs($this->writer())
            ->post(route('import.production'), ['file' => $txt])
            ->assertSessionHasErrors('file');
    }

    /** @test */
    public function import_requires_at_least_one_data_row(): void
    {
        $csv = "date,ore_hoisted,waste_hoisted,ore_crushed,ore_milled,gold_smelted,purity_percentage,fidelity_price\r\n";

        $this->actingAs($this->writer())
            ->post(route('import.production'), ['file' => $this->csvFile($csv)])
            ->assertSessionHasErrors('file');
    }
}
