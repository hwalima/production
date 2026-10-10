<?php

namespace Tests\Feature;

use App\Models\DailyProduction;
use App\Models\MiningRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function dashboard_renders_shift_comparison_with_mining_totals(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        DailyProduction::factory()->create([
            'date' => now()->toDateString(),
            'shift' => 'Day',
        ]);
        MiningRecord::create([
            'date' => now()->toDateString(),
            'shift' => 'Day',
            'mining_site' => 'Main Pit',
            'ore_hoisted_entered' => 100,
            'ore_hoisted' => 80,
            'waste_hoisted' => 10,
            'skip_factor' => 0.8,
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('shiftLabels', ['Day'])
            ->assertViewHas('shiftOreHoisted', [80.0]);
    }
}
