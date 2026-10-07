<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MachineRuntime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineRuntimeTest extends TestCase
{
    use RefreshDatabase;

    private function registerMachine(int $intervalHours = 20): Machine
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('machines.store'), [
                'machine_code' => 'BALL-01',
                'description' => 'Main ball mill',
                'service_interval_hours' => $intervalHours,
            ])
            ->assertRedirect();

        return Machine::where('machine_code', 'BALL-01')->firstOrFail();
    }

    private function recordRuntime(Machine $machine, string $start, string $end): void
    {
        $this->post(route('machines.runtimes.store', $machine), [
            'start_time' => $start,
            'end_time' => $end,
        ])->assertRedirect(route('machines.show', $machine));
    }

    public function test_registered_machine_can_have_multiple_runtime_sessions(): void
    {
        $machine = $this->registerMachine();

        $this->recordRuntime($machine, '2026-10-01T08:00', '2026-10-01T18:00');
        $this->recordRuntime($machine, '2026-10-02T08:00', '2026-10-02T20:00');

        $this->assertDatabaseCount('machines', 1);
        $this->assertDatabaseCount('machine_runtimes', 2);
        $this->assertSame(10.0, (float) MachineRuntime::firstOrFail()->hours_run);
        $this->assertSame(22.0, $machine->fresh()->hoursSinceLastService());
        $this->assertTrue($machine->fresh()->isServiceDue());
    }

    public function test_recording_service_resets_the_operating_hour_counter(): void
    {
        $machine = $this->registerMachine(30);
        $this->recordRuntime($machine, '2026-10-01T08:00', '2026-10-01T18:00');

        $this->post(route('machines.services.store', $machine), [
            'serviced_at' => '2026-10-01T19:00',
            'notes' => 'Replaced bearings',
        ])->assertRedirect(route('machines.show', $machine));

        $this->recordRuntime($machine, '2026-10-02T08:00', '2026-10-02T14:00');

        $machine = $machine->fresh();
        $this->assertSame(6.0, $machine->hoursSinceLastService());
        $this->assertFalse($machine->isServiceDue());
        $this->assertDatabaseCount('machine_services', 1);
    }

    public function test_service_interval_must_be_a_positive_whole_number_of_hours(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('machines.store'), [
                'machine_code' => 'BALL-01',
                'description' => 'Main ball mill',
                'service_interval_hours' => '1.5',
            ])
            ->assertSessionHasErrors('service_interval_hours');

        $this->assertDatabaseCount('machines', 0);
    }

    public function test_runtime_end_must_be_after_its_start(): void
    {
        $machine = $this->registerMachine();

        $this->post(route('machines.runtimes.store', $machine), [
            'start_time' => '2026-10-01T18:00',
            'end_time' => '2026-10-01T08:00',
        ])->assertSessionHasErrors('end_time');

        $this->assertDatabaseCount('machine_runtimes', 0);
    }

    public function test_runtime_sessions_for_one_machine_cannot_overlap(): void
    {
        $machine = $this->registerMachine();
        $this->recordRuntime($machine, '2026-10-01T08:00', '2026-10-01T18:00');

        $this->post(route('machines.runtimes.store', $machine), [
            'start_time' => '2026-10-01T17:00',
            'end_time' => '2026-10-01T20:00',
        ])->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('machine_runtimes', 1);
    }

    public function test_read_only_users_can_view_but_do_not_see_machine_write_actions(): void
    {
        $machine = Machine::create([
            'machine_code' => 'BALL-01',
            'description' => 'Main ball mill',
            'service_interval_hours' => 20,
        ]);

        $this->actingAs(User::factory()->create(['role' => 'viewer']))
            ->get(route('machines.show', $machine))
            ->assertOk()
            ->assertDontSee('Record Runtime')
            ->assertDontSee('Record Completed Service')
            ->assertDontSee('Edit Machine');
    }
}
