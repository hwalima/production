<?php

namespace Tests\Feature;

use App\Models\MachineRuntime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineRuntimeTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'machine_code' => 'BALL-01',
            'description' => 'Main ball mill',
            'start_time' => '2026-09-30T09:23',
            'end_time' => '2026-10-06T09:23',
            'service_after_hours' => '30',
        ], $overrides);
    }

    public function test_service_interval_is_used_as_integer_days_when_creating_and_updating(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('machines.store'), $this->payload())
            ->assertRedirect(route('machines.index'));

        $machine = MachineRuntime::firstOrFail();
        $this->assertSame('2026-11-05', $machine->next_service_date->toDateString());

        $this->put(route('machines.update', $machine), $this->payload([
            'service_after_hours' => '5',
        ]))->assertRedirect(route('machines.index'));

        $this->assertSame('2026-10-11', $machine->fresh()->next_service_date->toDateString());
    }

    public function test_service_interval_rejects_fractional_days(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('machines.store'), $this->payload([
                'service_after_hours' => '1.5',
            ]))
            ->assertSessionHasErrors('service_after_hours');

        $this->assertDatabaseCount('machine_runtimes', 0);
    }
}
