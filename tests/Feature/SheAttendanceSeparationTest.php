<?php

namespace Tests\Feature;

use App\Models\MiningDepartment;
use App\Models\SheIndicator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SheAttendanceSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_she_capture_accepts_environmental_incidents_and_descriptions(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $department = MiningDepartment::create(['name' => 'Underground', 'is_active' => true]);

        $this->actingAs($user)->get(route('she.indicators.create'))
            ->assertOk()
            ->assertSee('Environmental Incidents & Penalties')
            ->assertSee('Description')
            ->assertDontSee('name="leave"', false)
            ->assertDontSee('name="offdays"', false)
            ->assertDontSee('name="sick"', false)
            ->assertDontSee('name="awol"', false)
            ->assertDontSee('name="terminations"', false);

        $this->post(route('she.indicators.store'), [
            'date' => '2026-10-12',
            'mining_department_id' => $department->id,
            'environmental_incidents_penalties' => 2,
            'environmental_incidents_penalties_description' => 'Two discharge penalties recorded',
            'lti' => 1,
            'lti_description' => 'Fall of ground incident',
        ])->assertRedirect(route('she.index'));

        $record = SheIndicator::firstOrFail();
        $this->assertEquals(2, $record->environmental_incidents_penalties);
        $this->assertSame('Two discharge penalties recorded', $record->environmental_incidents_penalties_description);
        $this->assertSame('Fall of ground incident', $record->lti_description);
    }

    public function test_she_page_offers_a_date_filtered_pdf_download(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('she.index'))
            ->assertOk()
            ->assertSee('Export PDF');

        $pdfMock = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdfMock->shouldReceive('loadView')->once()->andReturnSelf();
        $pdfMock->shouldReceive('setPaper')->once()->with('a4', 'landscape')->andReturnSelf();
        $pdfMock->shouldReceive('download')->once()->andReturn(
            response()->make('%PDF-1.4 fake', 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="she-report-2026-10-01-to-2026-10-13.pdf"',
            ])
        );
        $this->app->instance('dompdf.wrapper', $pdfMock);

        $this->get(route('she.pdf', ['from' => '2026-10-01', 'to' => '2026-10-13']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
