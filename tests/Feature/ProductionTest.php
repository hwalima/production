<?php

namespace Tests\Feature;

use App\Models\DailyProduction;
use App\Models\MiningRecord;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function productionPayload(array $overrides = []): array
    {
        return array_merge([
            'date'              => '2026-04-14',
            'shift'             => 'Day',
            'mining_site'       => 'Main Pit',
            'ore_hoisted'       => 0,
            'ore_hoisted_target'=> null,
            'waste_hoisted'     => 0,
            'ore_crushed'       => '80.00',
            'ore_milled'        => '75.00',
            'ro_mine_milled'    => '25.00',
            'sanda_milled'      => '50.00',
            'sanda_milled_manual' => '0',
            'gold_smelted'      => '2.50',
            'purity_percentage' => '85.00',
            'fidelity_price'    => '90000.00',
        ], $overrides);
    }

    /** @test */
    public function production_index_requires_auth(): void
    {
        $this->get(route('production.index'))->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_view_production_index(): void
    {
        $this->actingAs($this->adminUser())
            ->get(route('production.index'))
            ->assertOk();
    }

    /** @test */
    public function can_create_a_production_record(): void
    {
        $this->actingAs($this->adminUser())
            ->post(route('production.store'), $this->productionPayload())
            ->assertRedirect(route('production.index'));

        // SQLite stores dates as datetime strings
        $this->assertDatabaseCount('daily_productions', 1);
        $this->assertTrue(DailyProduction::whereDate('date', '2026-04-14')->exists());
    }

    /** @test */
    public function uncrushed_stockpile_is_calculated_on_store(): void
    {
        MiningRecord::create([
            'date' => '2026-04-14',
            'shift' => 'Day',
            'mining_site' => 'Main Pit',
            'ore_hoisted_entered' => 100,
            'ore_hoisted' => 100,
            'waste_hoisted' => 10,
            'skip_factor' => 1,
        ]);
        $this->actingAs($this->adminUser())
            ->post(route('production.store'), $this->productionPayload([
                'ore_crushed' => '80',
            ]));

        // No previous record → 0 + 100 - 80 = 20
        $record = DailyProduction::first();
        $this->assertEquals(20.0, (float) $record->uncrushed_stockpile);
    }

    /** @test */
    public function second_record_stockpile_carries_over(): void
    {
        $this->actingAs($this->adminUser());

        MiningRecord::create([
            'date' => '2026-04-13', 'shift' => 'Day', 'mining_site' => 'Main Pit',
            'ore_hoisted_entered' => 100, 'ore_hoisted' => 100, 'waste_hoisted' => 10, 'skip_factor' => 1,
        ]);
        MiningRecord::create([
            'date' => '2026-04-14', 'shift' => 'Day', 'mining_site' => 'Main Pit',
            'ore_hoisted_entered' => 60, 'ore_hoisted' => 60, 'waste_hoisted' => 5, 'skip_factor' => 1,
        ]);
        $this->post(route('production.store'), $this->productionPayload([
            'date'        => '2026-04-13',
            'ore_crushed' => '80',
        ]));

        $this->post(route('production.store'), $this->productionPayload([
            'date'        => '2026-04-14',
            'ore_crushed' => '50',
        ]));

        // First record: uncrushed_stockpile = 20
        // Second: prev.uncrushed_stockpile (20) + 60 - 50 = 30
        $second = DailyProduction::orderByDesc('date')->first();
        $this->assertEquals(30.0, (float) $second->uncrushed_stockpile);
    }

    /** @test */
    public function mining_entered_before_plant_record_is_carried_into_later_stockpile(): void
    {
        MiningRecord::create([
            'date' => '2026-04-13',
            'shift' => 'Day',
            'mining_site' => 'Main Pit',
            'ore_hoisted_entered' => 100,
            'ore_hoisted' => 100,
            'waste_hoisted' => 10,
            'skip_factor' => 1,
        ]);

        $this->actingAs($this->adminUser())
            ->post(route('production.store'), $this->productionPayload([
                'date' => '2026-04-14',
                'ore_crushed' => '70',
            ]));

        $this->assertEquals(30.0, (float) DailyProduction::first()->uncrushed_stockpile);
    }

    /** @test */
    public function can_view_a_production_record(): void
    {
        $record = DailyProduction::create($this->productionPayload([
            'uncrushed_stockpile' => 20,
            'unmilled_stockpile'  => 5,
        ]));

        $this->actingAs($this->adminUser())
            ->get(route('production.show', $record))
            ->assertOk()
            ->assertSee('14 Apr 2026');
    }

    /** @test */
    public function can_update_a_production_record(): void
    {
        $record = DailyProduction::create($this->productionPayload([
            'uncrushed_stockpile' => 20,
            'unmilled_stockpile'  => 5,
        ]));

        $this->actingAs($this->adminUser())
            ->put(route('production.update', $record), $this->productionPayload([
                'ore_milled' => '70.00',
                'ro_mine_milled' => '20.00',
                'sanda_milled' => '50.00',
            ]))
            ->assertRedirect(route('production.index'));

        $this->assertDatabaseHas('daily_productions', ['ore_milled' => '70.00']);
    }

    /** @test */
    public function can_delete_a_production_record(): void
    {
        $record = DailyProduction::create($this->productionPayload([
            'uncrushed_stockpile' => 20,
            'unmilled_stockpile'  => 5,
        ]));

        $this->actingAs($this->adminUser())
            ->delete(route('production.destroy', $record))
            ->assertRedirect(route('production.index'));

        $this->assertDatabaseMissing('daily_productions', ['id' => $record->id]);
    }

    /** @test */
    public function mining_record_uses_configured_skip_factor_and_feeds_plant_stockpile(): void
    {
        Setting::create(['key' => 'skip_factor', 'value' => '0.8']);
        $user = $this->adminUser();

        $this->actingAs($user)
            ->post(route('assay.mining.store'), [
                'date' => '2026-04-14',
                'shift' => 'Day',
                'mining_site' => 'Main Pit',
                'ore_hoisted_entered' => '100',
                'waste_hoisted' => '10',
            ])
            ->assertRedirect(route('assay.index', ['tab' => 'mining']));

        $this->assertDatabaseHas('mining_records', [
            'ore_hoisted_entered' => '100.00',
            'ore_hoisted' => '80.00',
            'skip_factor' => '0.8000',
        ]);
        $this->get(route('assay.index', ['tab' => 'mining']))
            ->assertOk()
            ->assertSee('Adjusted Ore');

        $this->post(route('production.store'), $this->productionPayload([
            'ore_crushed' => '70',
        ]));
        $this->assertEquals(10.0, (float) DailyProduction::first()->uncrushed_stockpile);
    }

    /** @test */
    public function manually_edited_sanda_milled_is_preserved(): void
    {
        $this->actingAs($this->adminUser())
            ->post(route('production.store'), $this->productionPayload([
                'ore_milled' => '100',
                'ro_mine_milled' => '40',
                'sanda_milled' => '55',
                'sanda_milled_manual' => '1',
            ]))
            ->assertRedirect(route('production.index'));

        $record = DailyProduction::first();
        $this->assertEquals(55.0, (float) $record->sanda_milled);
        $this->assertTrue($record->sanda_milled_manual);
    }

    /** @test */
    public function sanda_milled_is_calculated_server_side_when_no_manual_value_is_submitted(): void
    {
        $payload = $this->productionPayload([
            'ore_milled' => '100',
            'ro_mine_milled' => '40',
        ]);
        unset($payload['sanda_milled']);

        $this->actingAs($this->adminUser())
            ->post(route('production.store'), $payload)
            ->assertRedirect(route('production.index'));

        $record = DailyProduction::first();
        $this->assertEquals(60.0, (float) $record->sanda_milled);
        $this->assertFalse($record->sanda_milled_manual);
    }

    /** @test */
    public function date_field_is_required(): void
    {
        $this->actingAs($this->adminUser())
            ->post(route('production.store'), $this->productionPayload(['date' => '']))
            ->assertSessionHasErrors('date');
    }
}
