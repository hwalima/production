<?php

namespace Tests\Feature;

use App\Models\BlastingRecord;
use App\Models\Consumable;
use App\Models\ConsumableStockMovement;
use App\Models\LabourEnergy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountsReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_accounts_report_totals_all_seven_cost_categories_and_lists_stock_issues(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        LabourEnergy::create([
            'date' => '2026-10-10',
            'zesa_cost' => 40,
            'diesel_cost' => 5,
            'labour_cost' => 30,
            'mining_cost' => 10,
            'milling_cost' => 20,
            'maintenance_cost' => 50,
            'transport_cost' => 60,
        ]);

        $item = Consumable::create([
            'name' => 'Test explosive',
            'category' => 'blasting',
            'purchase_unit' => 'box',
            'use_unit' => 'each',
            'units_per_pack' => 10,
            'pack_cost' => 31.25,
        ]);

        ConsumableStockMovement::create([
            'consumable_id' => $item->id,
            'type' => 'usage',
            'direction' => 'out',
            'quantity' => 2,
            'unit_cost' => 6.25,
            'total_cost' => 12.50,
            'movement_date' => '2026-10-10',
        ]);
        ConsumableStockMovement::create([
            'consumable_id' => $item->id,
            'type' => 'purchase',
            'direction' => 'in',
            'quantity' => 100,
            'unit_cost' => 6.25,
            'total_cost' => 312.50,
            'movement_date' => '2026-10-10',
        ]);
        ConsumableStockMovement::create([
            'consumable_id' => $item->id,
            'type' => 'adjustment',
            'direction' => 'out',
            'quantity' => 1,
            'unit_cost' => 6.25,
            'total_cost' => 6.25,
            'movement_date' => '2026-10-10',
        ]);

        $response = $this->actingAs($user)->get(route('reports.accounts', [
            'from' => '2026-10-01',
            'to' => '2026-10-31',
        ]));

        $response->assertOk()
            ->assertSee('Mining cost')
            ->assertSee('Milling cost')
            ->assertSee('Labour')
            ->assertSee('Power')
            ->assertSee('Consumables')
            ->assertSee('Maintenance')
            ->assertSee('Transport')
            ->assertSee('Test explosive');
        $this->assertEquals(227.5, (float) $response->viewData('totalCosts'));
        $this->assertEquals(12.5, (float) $response->viewData('totalConsumablesCost'));
        $this->assertCount(1, $response->viewData('consumableIssues'));

        $this->actingAs($user)->get(route('reports.accounts.pdf', [
            'from' => '2026-10-01',
            'to' => '2026-10-31',
        ]))->assertOk();
    }

    public function test_activity_records_without_stock_issues_are_not_misreported_as_consumables_cost(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        BlastingRecord::create([
            'fractures' => 5,
            'fuse' => 6,
            'carmes_ieds' => 4,
            'power_cords' => 5,
            'anfo' => 12.5,
            'oil' => 2,
            'drill_bits' => 1,
            'date' => '2026-10-10',
        ]);

        $response = $this->actingAs($user)->get(route('reports.accounts', [
            'from' => '2026-10-01',
            'to' => '2026-10-31',
        ]));

        $response->assertOk()
            ->assertSee('No consumable stock issues were recorded for this period')
            ->assertSee('Blasting/drilling activity records do not issue inventory');
        $this->assertEquals(0, (float) $response->viewData('totalConsumablesCost'));
    }

    public function test_labour_energy_form_persists_the_new_cost_categories(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->post(route('labour-energy.store'), [
            'date' => '2026-10-11',
            'zesa_cost' => 10,
            'diesel_cost' => 5,
            'mining_cost' => 20,
            'milling_cost' => 30,
            'maintenance_cost' => 40,
            'transport_cost' => 50,
        ])->assertRedirect(route('labour-energy.index'));

        $this->assertDatabaseHas('labour_energy', [
            'mining_cost' => 20,
            'milling_cost' => 30,
            'maintenance_cost' => 40,
            'transport_cost' => 50,
        ]);
    }
}
