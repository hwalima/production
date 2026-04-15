<?php

namespace Tests\Feature;

use App\Models\DailyProduction;
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
            'ore_hoisted'       => '100.00',
            'waste_hoisted'     => '10.00',
            'ore_crushed'       => '80.00',
            'ore_milled'        => '75.00',
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
    public function profit_is_auto_calculated_on_store(): void
    {
        $this->actingAs($this->adminUser())
            ->post(route('production.store'), $this->productionPayload([
                'gold_smelted'      => '2.5',
                'fidelity_price'    => '90000',
                'purity_percentage' => '85',
            ]));

        // 2.5 × 90000 × 0.85 = 191250
        $record = DailyProduction::first();
        $this->assertEquals(191250.0, (float) $record->profit_calculated);
    }

    /** @test */
    public function uncrushed_stockpile_is_calculated_on_store(): void
    {
        $this->actingAs($this->adminUser())
            ->post(route('production.store'), $this->productionPayload([
                'ore_hoisted' => '100',
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

        $this->post(route('production.store'), $this->productionPayload([
            'date'        => '2026-04-13',
            'ore_hoisted' => '100',
            'ore_crushed' => '80',
        ]));

        $this->post(route('production.store'), $this->productionPayload([
            'date'        => '2026-04-14',
            'ore_hoisted' => '60',
            'ore_crushed' => '50',
        ]));

        // First record: uncrushed_stockpile = 20
        // Second: prev.uncrushed_stockpile (20) + 60 - 50 = 30
        $second = DailyProduction::orderByDesc('date')->first();
        $this->assertEquals(30.0, (float) $second->uncrushed_stockpile);
    }

    /** @test */
    public function can_view_a_production_record(): void
    {
        $record = DailyProduction::create($this->productionPayload([
            'uncrushed_stockpile' => 20,
            'unmilled_stockpile'  => 5,
            'profit_calculated'   => 191250,
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
            'profit_calculated'   => 191250,
        ]));

        $this->actingAs($this->adminUser())
            ->put(route('production.update', $record), $this->productionPayload([
                'ore_hoisted' => '200.00',
            ]))
            ->assertRedirect(route('production.index'));

        $this->assertDatabaseHas('daily_productions', ['ore_hoisted' => '200.00']);
    }

    /** @test */
    public function can_delete_a_production_record(): void
    {
        $record = DailyProduction::create($this->productionPayload([
            'uncrushed_stockpile' => 20,
            'unmilled_stockpile'  => 5,
            'profit_calculated'   => 191250,
        ]));

        $this->actingAs($this->adminUser())
            ->delete(route('production.destroy', $record))
            ->assertRedirect(route('production.index'));

        $this->assertDatabaseMissing('daily_productions', ['id' => $record->id]);
    }

    /** @test */
    public function date_field_is_required(): void
    {
        $this->actingAs($this->adminUser())
            ->post(route('production.store'), $this->productionPayload(['date' => '']))
            ->assertSessionHasErrors('date');
    }
}
