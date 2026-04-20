<?php

namespace Tests\Feature;

use App\Models\AssayResult;
use App\Models\BlastingRecord;
use App\Models\Consumable;
use App\Models\ConsumableStockMovement;
use App\Models\DailyProduction;
use App\Models\DrillingRecord;
use App\Models\LabourEnergy;
use App\Models\MachineRuntime;
use App\Models\SheIndicator;
use App\Models\MiningDepartment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for all 13 Analytics KPIs, routes, auth, CSV export and PDF export.
 *
 * Fixed date window: 2026-01-01 → 2026-01-31 (avoids time-of-day drift).
 * We use exact numeric assertions so regressions in formulas are caught immediately.
 */
class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private string $from = '2026-01-01';
    private string $to   = '2026-01-31';

    // ── helpers ────────────────────────────────────────────────────────────

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function url(array $extra = []): string
    {
        return route('analytics.index', array_merge(['from' => $this->from, 'to' => $this->to], $extra));
    }

    /**
     * Create the canonical January data set used across most tests.
     *
     * Day A – 2026-01-10:
     *   gold=5 g, milled=500 t, fire_assay=0.012 g/t
     *   recovery = (5 / (500 * 0.012)) * 100 = 83.3 %
     *   implied grade = 5/500 = 0.01
     *   labour/zesa/diesel = 5000+1000+2000 = 8000
     *
     * Day B – 2026-01-20:
     *   gold=9 g, milled=600 t, fire_assay=0.015 g/t
     *   recovery = (9 / (600 * 0.015)) * 100 = 100.0 %
     *   implied grade = 9/600 = 0.015
     *   labour/zesa/diesel = 6000+1500+2500 = 10000
     *
     * Period total: gold=14, milled=1100, total_cost=18000
     * AISC = 18000/14 = 1285.71
     * avgCostPerTonne = 18000/1100 = 16.36
     * avgMillRecovery = (83.3 + 100.0) / 2 = 91.7 (PHP float rounds 91.65 → 91.7)
     */
    private function dept(): MiningDepartment
    {
        return MiningDepartment::firstOrCreate(['name' => 'Test Dept']);
    }

    /** Base DailyProduction payload satisfying all NOT NULL constraints. */
    private function prod(array $overrides = []): array
    {
        return array_merge([
            'ore_hoisted'         => 100,
            'waste_hoisted'       => 5,
            'ore_crushed'         => 90,
            'ore_milled'          => 500,
            'gold_smelted'        => 5,
            'purity_percentage'   => 90,
            'fidelity_price'      => 95000,
            'uncrushed_stockpile' => 0,
            'unmilled_stockpile'  => 0,
        ], $overrides);
    }

    private function seedJanData(): void
    {
        DailyProduction::create($this->prod([
            'date'                => '2026-01-10',
            'ore_hoisted'         => 120,
            'waste_hoisted'       => 10,
            'ore_crushed'         => 100,
            'ore_milled'          => 500,
            'gold_smelted'        => 5,
            'purity_percentage'   => 90,
            'fidelity_price'      => 95000,
            'uncrushed_stockpile' => 20,
            'unmilled_stockpile'  => 5,
        ]));
        DailyProduction::create($this->prod([
            'date'                => '2026-01-20',
            'ore_hoisted'         => 150,
            'waste_hoisted'       => 15,
            'ore_crushed'         => 120,
            'ore_milled'          => 600,
            'gold_smelted'        => 9,
            'purity_percentage'   => 92,
            'fidelity_price'      => 96000,
            'uncrushed_stockpile' => 30,
            'unmilled_stockpile'  => 8,
        ]));

        // Fire assay results for both days
        AssayResult::create(['type' => 'fire_assay', 'date' => '2026-01-10', 'assay_value' => 0.012]);
        AssayResult::create(['type' => 'fire_assay', 'date' => '2026-01-20', 'assay_value' => 0.015]);

        // Labour & energy costs
        LabourEnergy::create(['date' => '2026-01-10', 'labour_cost' => 5000, 'zesa_cost' => 1000, 'diesel_cost' => 2000]);
        LabourEnergy::create(['date' => '2026-01-20', 'labour_cost' => 6000, 'zesa_cost' => 1500, 'diesel_cost' => 2500]);
    }

    // ── 0. Access control ──────────────────────────────────────────────────

    /** @test */
    public function analytics_index_redirects_guests_to_login(): void
    {
        $this->get(route('analytics.index'))->assertRedirect(route('login'));
    }

    /** @test */
    public function analytics_export_csv_redirects_guests_to_login(): void
    {
        $this->get(route('analytics.export'))->assertRedirect(route('login'));
    }

    /** @test */
    public function analytics_export_pdf_redirects_guests_to_login(): void
    {
        $this->get(route('analytics.export.pdf'))->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_admin_can_access_analytics_index(): void
    {
        $this->actingAs($this->admin())
            ->get($this->url())
            ->assertOk()
            ->assertViewIs('analytics.index');
    }

    // ── 0b. Date-filter validation ─────────────────────────────────────────

    /** @test */
    public function invalid_date_string_fails_validation(): void
    {
        $this->actingAs($this->admin())
            ->get(route('analytics.index', ['from' => 'not-a-date', 'to' => '2026-01-31']))
            ->assertSessionHasErrors('from');
    }

    /** @test */
    public function when_from_exceeds_to_it_is_clamped_to_start_of_to_month(): void
    {
        // from=2026-01-31, to=2026-01-05  → from should be clamped to 2026-01-01
        $response = $this->actingAs($this->admin())
            ->get(route('analytics.index', ['from' => '2026-01-31', 'to' => '2026-01-05']));

        $response->assertOk();
        $this->assertEquals('2026-01-01', $response->viewData('from'));
    }

    /** @test */
    public function date_range_is_persisted_to_session(): void
    {
        $this->actingAs($this->admin())
            ->get(route('analytics.index', ['from' => '2026-01-01', 'to' => '2026-01-31']));

        $this->assertEquals('2026-01-01', session('analytics_from'));
        $this->assertEquals('2026-01-31', session('analytics_to'));
    }

    /** @test */
    public function session_values_are_used_when_no_date_params_provided(): void
    {
        $this->actingAs($this->admin());

        // First request: seeds the session
        $this->get(route('analytics.index', ['from' => '2026-02-01', 'to' => '2026-02-28']));

        // Second request: no params → should use session
        $response = $this->get(route('analytics.index'));
        $this->assertEquals('2026-02-01', $response->viewData('from'));
        $this->assertEquals('2026-02-28', $response->viewData('to'));
    }

    // ── 1. Mill Recovery % ─────────────────────────────────────────────────

    /** @test */
    public function mill_recovery_is_computed_correctly_from_assay_and_production(): void
    {
        $this->seedJanData();

        $response = $this->actingAs($this->admin())->get($this->url());
        $response->assertOk();

        // Day A: (5 / (500 * 0.012)) * 100 = 83.3 %
        // Day B: (9 / (600 * 0.015)) * 100 = 100.0 %
        // avg   = (83.3 + 100.0) / 2 = 91.65

        $this->assertTrue($response->viewData('hasAssayData'));
        $this->assertEquals(83.3,  $response->viewData('recoveryTrendData')[0]);
        $this->assertEquals(100.0, $response->viewData('recoveryTrendData')[1]);
        // PHP rounds 91.65 → 91.7 due to IEEE 754 float representation (83.3 stored slightly below)
        $this->assertEquals(91.7, $response->viewData('avgMillRecovery'));
    }

    /** @test */
    public function mill_recovery_is_null_when_no_fire_assay_exists(): void
    {
        DailyProduction::create($this->prod(['date' => '2026-01-10']));

        $response = $this->actingAs($this->admin())->get($this->url());
        $response->assertOk();

        $this->assertNull($response->viewData('avgMillRecovery'));
        $this->assertFalse($response->viewData('hasAssayData'));
    }

    /** @test */
    public function non_fire_assay_results_are_excluded_from_mill_recovery(): void
    {
        DailyProduction::create($this->prod(['date' => '2026-01-10']));
        // This is NOT a fire_assay — should be ignored
        AssayResult::create(['type' => 'bottle_roll', 'date' => '2026-01-10', 'assay_value' => 0.050]);

        $response = $this->actingAs($this->admin())->get($this->url());
        $this->assertNull($response->viewData('avgMillRecovery'));
    }

    // ── 2. AISC per gram ───────────────────────────────────────────────────

    /** @test */
    public function aisc_per_gram_is_total_cost_divided_by_total_gold(): void
    {
        $this->seedJanData();

        // total costs = 8000 + 10000 = 18000, total gold = 5 + 9 = 14
        // AISC = round(18000 / 14, 2) = 1285.71
        $response = $this->actingAs($this->admin())->get($this->url());

        $this->assertEquals(1285.71, $response->viewData('avgAisc'));
    }

    /** @test */
    public function aisc_is_null_when_no_gold_is_produced(): void
    {
        DailyProduction::create($this->prod(['date' => '2026-01-10', 'ore_milled' => 70, 'gold_smelted' => 0]));

        $response = $this->actingAs($this->admin())->get($this->url());
        $this->assertNull($response->viewData('avgAisc'));
    }

    /** @test */
    public function aisc_monthly_labels_are_in_month_year_format(): void
    {
        $this->seedJanData();

        $response = $this->actingAs($this->admin())->get($this->url());
        $labels   = $response->viewData('aiscLabels');

        $this->assertNotEmpty($labels);
        $this->assertEquals('Jan 2026', $labels[0]);
    }

    // ── 3. Grade Reconciliation ────────────────────────────────────────────

    /** @test */
    public function grade_reconciliation_arrays_have_matching_length(): void
    {
        $this->seedJanData();

        $response = $this->actingAs($this->admin())->get($this->url());

        $labels  = $response->viewData('gradeRecLabels');
        $fire    = $response->viewData('gradeRecFire');
        $implied = $response->viewData('gradeRecImplied');

        $this->assertCount(count($labels), $fire);
        $this->assertCount(count($labels), $implied);
        $this->assertNotEmpty($labels);
    }

    /** @test */
    public function implied_grade_is_gold_divided_by_ore_milled(): void
    {
        $this->seedJanData();

        $response = $this->actingAs($this->admin())->get($this->url());
        $implied  = $response->viewData('gradeRecImplied');

        // Day A: 5 / 500 = 0.01
        $this->assertEquals(0.01, $implied[0]);
        // Day B: 9 / 600 = 0.015
        $this->assertEquals(0.015, $implied[1]);
    }

    /** @test */
    public function fire_assay_grade_appears_in_grade_reconciliation(): void
    {
        $this->seedJanData();

        $response = $this->actingAs($this->admin())->get($this->url());
        $fire     = $response->viewData('gradeRecFire');

        $this->assertEquals(0.012, $fire[0]); // Day A
        $this->assertEquals(0.015, $fire[1]); // Day B
    }

    // ── 4. Cost per tonne milled ───────────────────────────────────────────

    /** @test */
    public function cost_per_tonne_milled_is_total_cost_divided_by_total_milled(): void
    {
        $this->seedJanData();

        // total cost = 18000, total milled = 1100
        // CPT = round(18000 / 1100, 2) = 16.36
        $response = $this->actingAs($this->admin())->get($this->url());
        $this->assertEquals(16.36, $response->viewData('avgCostPerTonne'));
    }

    /** @test */
    public function cost_per_tonne_is_null_when_nothing_milled(): void
    {
        // No production data at all
        $response = $this->actingAs($this->admin())->get($this->url());
        $this->assertNull($response->viewData('avgCostPerTonne'));
    }

    // ── 5. MoM / YTD ──────────────────────────────────────────────────────

    /** @test */
    public function mom_gold_delta_is_correct_percentage_change(): void
    {
        // Dec 2025 (previous period): 10 g
        DailyProduction::create($this->prod(['date' => '2025-12-15', 'ore_hoisted' => 110, 'ore_milled' => 500, 'gold_smelted' => 10]));

        $this->seedJanData(); // Jan 2026: 14 g

        $response = $this->actingAs($this->admin())->get($this->url());

        // MoM gold delta: ((14 - 10) / 10) * 100 = 40.0 %
        $this->assertEquals(40.0, $response->viewData('momGoldDelta'));
    }

    /** @test */
    public function mom_gold_delta_is_null_when_no_prior_period_data(): void
    {
        $this->seedJanData(); // No December data

        $response = $this->actingAs($this->admin())->get($this->url());
        $this->assertNull($response->viewData('momGoldDelta'));
    }

    /** @test */
    public function ytd_gold_sums_from_jan_1_to_to_date(): void
    {
        $this->seedJanData(); // 5 + 9 = 14 g in Jan

        $response = $this->actingAs($this->admin())->get($this->url());

        // YTD gold = 14 (entire Jan 2026 within 2026 year)
        $this->assertEquals(14.0, $response->viewData('ytdGold'));
    }

    // ── 6. Stockpile balance ───────────────────────────────────────────────

    /** @test */
    public function stockpile_labels_and_values_match_daily_production_dates(): void
    {
        $this->seedJanData();

        $response = $this->actingAs($this->admin())->get($this->url());

        $stockLabels    = $response->viewData('stockLabels');
        $stockUncrushed = $response->viewData('stockUncrushed');
        $stockUnmilled  = $response->viewData('stockUnmilled');

        $this->assertCount(2, $stockLabels);
        $this->assertEquals('Jan 10', $stockLabels[0]);
        $this->assertEquals('Jan 20', $stockLabels[1]);
        $this->assertEquals(20.0,     $stockUncrushed[0]);
        $this->assertEquals(30.0,     $stockUncrushed[1]);
        $this->assertEquals(5.0,      $stockUnmilled[0]);
        $this->assertEquals(8.0,      $stockUnmilled[1]);
    }

    /** @test */
    public function latest_stockpile_values_reflect_last_production_row(): void
    {
        $this->seedJanData();

        $response = $this->actingAs($this->admin())->get($this->url());

        // Last row (Jan 20) has uncrushed=30, unmilled=8
        $this->assertEquals(30.0, $response->viewData('latestUncrushed'));
        $this->assertEquals(8.0,  $response->viewData('latestUnmilled'));
    }

    // ── 7. Blasting ────────────────────────────────────────────────────────

    /** @test */
    public function blasting_totals_are_sum_of_period_records(): void
    {
        BlastingRecord::create(['date' => '2026-01-10', 'anfo' => 500, 'oil' => 100, 'fractures' => 12, 'fuse' => 0, 'carmes_ieds' => 0, 'power_cords' => 0, 'drill_bits' => 0]);
        BlastingRecord::create(['date' => '2026-01-20', 'anfo' => 450, 'oil' =>  90, 'fractures' => 10, 'fuse' => 0, 'carmes_ieds' => 0, 'power_cords' => 0, 'drill_bits' => 0]);

        $response = $this->actingAs($this->admin())->get($this->url());

        $this->assertEquals(950.0,  $response->viewData('totalAnfo'));
        $this->assertEquals(190.0,  $response->viewData('totalOil'));
        $this->assertCount(2,        $response->viewData('blastLabels'));
    }

    /** @test */
    public function blasting_records_outside_range_are_excluded(): void
    {
        BlastingRecord::create(['date' => '2025-12-31', 'anfo' => 999, 'oil' => 200, 'fractures' => 5, 'fuse' => 0, 'carmes_ieds' => 0, 'power_cords' => 0, 'drill_bits' => 0]);
        BlastingRecord::create(['date' => '2026-01-10', 'anfo' => 400, 'oil' =>  80, 'fractures' => 8, 'fuse' => 0, 'carmes_ieds' => 0, 'power_cords' => 0, 'drill_bits' => 0]);

        $response = $this->actingAs($this->admin())->get($this->url());

        $this->assertEquals(400.0, $response->viewData('totalAnfo'));
        $this->assertEquals(80.0,  $response->viewData('totalOil'));
    }

    // ── 8. SHE Safety rates ────────────────────────────────────────────────

    /** @test */
    public function she_totals_are_computed_correctly_from_indicators(): void
    {
        $dept = $this->dept();
        SheIndicator::create([
            'date'                  => '2026-01-10',
            'mining_department_id'  => $dept->id,
            'lti'                   => 2,
            'nlti'                  => 3,
            'fatal_incident'        => 0,
            'medical_injury_case'   => 1,
            'sick'                  => 5,
            'leave'                 => 3,
            'awol'                  => 1,
        ]);
        SheIndicator::create([
            'date'                  => '2026-01-20',
            'mining_department_id'  => $dept->id,
            'lti'                   => 1,
            'nlti'                  => 2,
            'fatal_incident'        => 0,
            'medical_injury_case'   => 0,
            'sick'                  => 2,
            'leave'                 => 1,
            'awol'                  => 0,
        ]);

        $response = $this->actingAs($this->admin())->get($this->url());

        $this->assertEquals(3,  $response->viewData('totalLti'));
        $this->assertEquals(5,  $response->viewData('totalNlti'));
        $this->assertEquals(0,  $response->viewData('totalFatal'));
        $this->assertEquals(1,  $response->viewData('totalMedical'));
        $this->assertEquals(7,  $response->viewData('totalSick'));
        $this->assertEquals(4,  $response->viewData('totalLeave'));
        $this->assertEquals(1,  $response->viewData('totalAwol'));
        // totalAbsence = sick + leave + awol = 7 + 4 + 1 = 12
        $this->assertEquals(12, $response->viewData('totalAbsence'));
    }

    /** @test */
    public function she_monthly_buckets_aggregate_by_calendar_month(): void
    {
        $dept = $this->dept();
        SheIndicator::create(['date' => '2026-01-10', 'mining_department_id' => $dept->id, 'lti' => 2, 'nlti' => 0, 'fatal_incident' => 0, 'medical_injury_case' => 0, 'sick' => 0, 'leave' => 0, 'awol' => 0]);
        SheIndicator::create(['date' => '2026-01-25', 'mining_department_id' => $dept->id, 'lti' => 1, 'nlti' => 0, 'fatal_incident' => 0, 'medical_injury_case' => 0, 'sick' => 0, 'leave' => 0, 'awol' => 0]);

        $response = $this->actingAs($this->admin())->get($this->url());

        $monthLti = $response->viewData('sheMonthLti');
        $this->assertCount(1, $monthLti);        // one month bucket (Jan 2026)
        $this->assertEquals(3, $monthLti[0]);    // 2 + 1
    }

    // ── 9. Consumables burn rate ───────────────────────────────────────────

    /** @test */
    public function consumables_burn_rate_aggregates_by_category(): void
    {
        $consumable = Consumable::create([
            'name'          => 'ANFO Bag',
            'category'      => 'explosives',
            'purchase_unit' => 'bag',
            'use_unit'      => 'bag',
            'units_per_pack'=> 1,
            'pack_cost'     => 500,
        ]);

        ConsumableStockMovement::create([
            'consumable_id' => $consumable->id,
            'direction'     => 'out',
            'type'          => 'usage',
            'quantity'      => 10,
            'unit_cost'     => 500,
            'total_cost'    => 5000,
            'movement_date' => '2026-01-10',
        ]);
        ConsumableStockMovement::create([
            'consumable_id' => $consumable->id,
            'direction'     => 'out',
            'type'          => 'usage',
            'quantity'      => 5,
            'unit_cost'     => 500,
            'total_cost'    => 2500,
            'movement_date' => '2026-01-20',
        ]);
        // 'in' movement — must NOT count
        ConsumableStockMovement::create([
            'consumable_id' => $consumable->id,
            'direction'     => 'in',
            'type'          => 'purchase',
            'quantity'      => 50,
            'unit_cost'     => 500,
            'total_cost'    => 25000,
            'movement_date' => '2026-01-05',
        ]);

        $response = $this->actingAs($this->admin())->get($this->url());
        $burn     = $response->viewData('burnByCategory');

        $this->assertCount(1, $burn);
        $this->assertEquals('explosives', $burn[0]->category);
        $this->assertEquals(7500.0,       (float) $burn[0]->total_cost);
        $this->assertEquals(15.0,         (float) $burn[0]->total_qty);
    }

    /** @test */
    public function consumables_monthly_trend_groups_by_month(): void
    {
        $consumable = Consumable::create([
            'name'          => 'Drill Bit',
            'category'      => 'tooling',
            'purchase_unit' => 'piece',
            'use_unit'      => 'piece',
            'units_per_pack'=> 1,
            'pack_cost'     => 200,
        ]);
        ConsumableStockMovement::create([
            'consumable_id' => $consumable->id,
            'direction'     => 'out',
            'type'          => 'usage',
            'quantity'      => 3,
            'unit_cost'     => 200,
            'total_cost'    => 600,
            'movement_date' => '2026-01-15',
        ]);

        $response = $this->actingAs($this->admin())->get($this->url());

        $labels = $response->viewData('consumMonthLabels');
        $data   = $response->viewData('consumMonthData');

        $this->assertCount(1, $labels);
        $this->assertEquals('Jan 2026', $labels[0]);
        $this->assertEquals(600.0, $data[0]);
    }

    // ── 10. Drill metres ───────────────────────────────────────────────────

    /** @test */
    public function drill_metres_totals_are_summed_within_date_range(): void
    {
        DrillingRecord::create(['date' => '2026-01-10', 'advance' => 25.5, 'hole_count' => 5,  'end_name' => 'Level 5', 'drill_steel_length' => 1.5]);
        DrillingRecord::create(['date' => '2026-01-20', 'advance' => 30.0, 'hole_count' => 6,  'end_name' => 'Level 5', 'drill_steel_length' => 1.5]);
        DrillingRecord::create(['date' => '2025-12-31', 'advance' => 99.0, 'hole_count' => 20, 'end_name' => 'Level 4', 'drill_steel_length' => 1.5]); // outside range

        $response = $this->actingAs($this->admin())->get($this->url());

        $this->assertEquals(55.5, $response->viewData('totalAdvance'));
        $this->assertEquals(11,   $response->viewData('totalHoles'));
        $this->assertCount(2,      $response->viewData('drillLabels'));
        // avgAdvPerDay = round(55.5 / 2, 2) = 27.75
        $this->assertEquals(27.75, $response->viewData('avgAdvPerDay'));
    }

    // ── 11. SPC control chart ──────────────────────────────────────────────

    /** @test */
    public function spc_mean_std_ucl_lcl_are_computed_correctly(): void
    {
        // 4 production rows with deterministic grades
        // grade = gold / milled
        // grades: 0.01, 0.02, 0.01, 0.02 → mean=0.015
        $rows = [
            ['date' => '2026-01-05', 'gold_smelted' => 10, 'ore_milled' => 1000], // grade=0.01
            ['date' => '2026-01-10', 'gold_smelted' => 20, 'ore_milled' => 1000], // grade=0.02
            ['date' => '2026-01-15', 'gold_smelted' => 10, 'ore_milled' => 1000], // grade=0.01
            ['date' => '2026-01-20', 'gold_smelted' => 20, 'ore_milled' => 1000], // grade=0.02
        ];
        foreach ($rows as $row) {
            DailyProduction::create($this->prod(array_merge($row, [
                'ore_hoisted'  => 1200,
                'waste_hoisted'=> 50,
                'ore_crushed'  => 1100,
            ])));
        }

        $response = $this->actingAs($this->admin())->get($this->url());

        $mean = $response->viewData('spcMean');
        $std  = $response->viewData('spcStd');
        $ucl  = $response->viewData('spcUcl');
        $lcl  = $response->viewData('spcLcl');

        // mean = (0.01+0.02+0.01+0.02)/4 = 0.015
        $this->assertEquals(0.015, round($mean, 3));

        // population std = sqrt(mean_of_squared_deviations)
        // deviations: -0.005, 0.005, -0.005, 0.005
        // variance = (4 * 0.000025) / 4 = 0.000025 → std = 0.005
        $this->assertEquals(0.005, round($std, 3));

        // UCL = mean + 2*std = 0.015 + 0.010 = 0.025
        $this->assertEquals(0.025, $ucl);

        // LCL = max(0, mean - 2*std) = max(0, 0.005) = 0.005
        $this->assertEquals(0.005, $lcl);

        // All 4 labels present
        $this->assertCount(4, $response->viewData('spcLabels'));
    }

    /** @test */
    public function spc_lcl_cannot_go_below_zero(): void
    {
        // grades all very small → LCL would go negative without max(0, ...)
        $rows = [
            ['date' => '2026-01-05', 'gold_smelted' => 0.1, 'ore_milled' => 500],
            ['date' => '2026-01-10', 'gold_smelted' => 5.0, 'ore_milled' => 500],
        ];
        foreach ($rows as $row) {
            DailyProduction::create($this->prod(array_merge($row, [
                'ore_hoisted'  => 600,
                'waste_hoisted'=> 10,
                'ore_crushed'  => 550,
            ])));
        }

        $response = $this->actingAs($this->admin())->get($this->url());
        $this->assertGreaterThanOrEqual(0.0, $response->viewData('spcLcl'));
    }

    // ── 12. Predictive maintenance ─────────────────────────────────────────

    /** @test */
    public function machines_overdue_for_service_have_status_overdue(): void
    {
        MachineRuntime::create([
            'machine_code'       => 'CRUSHER_01',
            'description'        => 'Primary Crusher',
            'service_after_hours'=> 2160,
            'next_service_date'  => Carbon::now()->subDay()->toDateString(),
            'start_time'         => Carbon::now()->subDays(100)->toDateTimeString(),
            'end_time'           => Carbon::now()->subDays(99)->toDateTimeString(),
        ]);

        $response = $this->actingAs($this->admin())->get($this->url());

        $scores = $response->viewData('machineScores');
        $crusher = collect($scores)->firstWhere('code', 'CRUSHER_01');

        $this->assertNotNull($crusher);
        $this->assertEquals('overdue', $crusher['status']);
        $this->assertLessThan(0, $crusher['days_to_service']);
    }

    /** @test */
    public function machines_due_within_7_days_have_status_due_soon(): void
    {
        MachineRuntime::create([
            'machine_code'       => 'MILL_01',
            'description'        => 'Ball Mill',
            'service_after_hours'=> 2160,
            'next_service_date'  => Carbon::now()->addDays(5)->toDateString(),
            'start_time'         => Carbon::now()->subDays(80)->toDateTimeString(),
            'end_time'           => Carbon::now()->subDays(79)->toDateTimeString(),
        ]);

        $response = $this->actingAs($this->admin())->get($this->url());

        $scores = $response->viewData('machineScores');
        $mill   = collect($scores)->firstWhere('code', 'MILL_01');

        $this->assertNotNull($mill);
        $this->assertEquals('due_soon', $mill['status']);
    }

    /** @test */
    public function machines_with_no_service_date_have_status_unknown(): void
    {
        MachineRuntime::create([
            'machine_code'       => 'PUMP_01',
            'description'        => 'Slurry Pump',
            'service_after_hours'=> 0,
            'next_service_date'  => null,
            'start_time'         => Carbon::now()->subDays(10)->toDateTimeString(),
            'end_time'           => Carbon::now()->subDays(9)->toDateTimeString(),
        ]);

        $response = $this->actingAs($this->admin())->get($this->url());

        $scores = $response->viewData('machineScores');
        $pump   = collect($scores)->firstWhere('code', 'PUMP_01');

        $this->assertEquals('unknown', $pump['status']);
        $this->assertNull($pump['days_to_service']);
    }

    // ── 13. Anomaly detection ──────────────────────────────────────────────

    /** @test */
    public function anomaly_detection_flags_outlier_gold_grade_beyond_2_sigma(): void
    {
        // 6 normal rows (grade ≈ 0.01) plus 1 extreme outlier (grade = 0.10)
        $normals = [
            ['2026-01-02', 10, 1000],
            ['2026-01-04', 10, 1000],
            ['2026-01-06', 10, 1000],
            ['2026-01-08', 11, 1000],
            ['2026-01-10', 10, 1000],
            ['2026-01-12',  9, 1000],
            ['2026-01-14', 100, 1000], // outlier: grade=0.1
        ];
        foreach ($normals as [$date, $gold, $milled]) {
            DailyProduction::create($this->prod([
                'date'         => $date,
                'gold_smelted' => $gold,
                'ore_milled'   => $milled,
                'ore_hoisted'  => 1200,
                'waste_hoisted'=> 50,
                'ore_crushed'  => 1100,
            ]));
        }

        $response  = $this->actingAs($this->admin())->get($this->url());
        $anomalies = $response->viewData('anomalies');

        $this->assertNotEmpty($anomalies, 'Expected at least one anomaly for the outlier row.');

        // The outlier should have the highest absolute z-score (sorted first)
        $this->assertGreaterThan(2.0, abs($anomalies[0]['z']));
        $this->assertEquals('above', $anomalies[0]['dir']);
    }

    /** @test */
    public function anomaly_detection_requires_more_than_5_data_points(): void
    {
        // Only 3 rows — anomaly detection should not fire for implied grade
        foreach (['2026-01-02', '2026-01-04', '2026-01-06'] as $date) {
            DailyProduction::create($this->prod(['date' => $date, 'ore_milled' => 500, 'gold_smelted' => 5]));
        }

        $response  = $this->actingAs($this->admin())->get($this->url());
        $anomalies = $response->viewData('anomalies');

        // Grade anomalies need >5 points; gold anomalies need goldStd > 0.
        // With identical gold values (5,5,5) goldStd=0 so no anomalies either.
        $this->assertEmpty($anomalies);
    }

    // ── 14. CSV export ─────────────────────────────────────────────────────

    /** @test */
    public function csv_export_requires_authentication(): void
    {
        $this->get(route('analytics.export'))->assertRedirect(route('login'));
    }

    /** @test */
    public function csv_export_returns_downloadable_csv_file(): void
    {
        $this->seedJanData();

        $response = $this->actingAs($this->admin())
            ->get(route('analytics.export', ['from' => $this->from, 'to' => $this->to]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition') ?? '');
    }

    /** @test */
    public function csv_export_filename_contains_date_range(): void
    {
        $this->seedJanData();

        $response = $this->actingAs($this->admin())
            ->get(route('analytics.export', ['from' => $this->from, 'to' => $this->to]));

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('2026-01-01', $disposition);
        $this->assertStringContainsString('2026-01-31', $disposition);
    }

    /** @test */
    public function csv_export_contains_production_header_row(): void
    {
        $this->seedJanData();

        $response = $this->actingAs($this->admin())
            ->get(route('analytics.export', ['from' => $this->from, 'to' => $this->to]));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Gold Smelted', $content);
        $this->assertStringContainsString('Ore Milled',   $content);
    }

    // ── 15. PDF export ─────────────────────────────────────────────────────

    /** @test */
    public function pdf_export_requires_authentication(): void
    {
        $this->get(route('analytics.export.pdf'))->assertRedirect(route('login'));
    }

    /** @test */
    public function pdf_export_returns_pdf_download_with_mocked_renderer(): void
    {
        $this->seedJanData();

        // Mock the dompdf.wrapper binding so we don't need a real PDF renderer in CI
        $pdfMock = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdfMock->shouldReceive('loadView')->once()->andReturnSelf();
        $pdfMock->shouldReceive('setPaper')->once()->andReturnSelf();
        $pdfMock->shouldReceive('setOption')->once()->andReturnSelf();
        $pdfMock->shouldReceive('download')->once()->andReturn(
            response()->make('%PDF-1.4 fake', 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="analytics-2026-01-01-to-2026-01-31.pdf"',
            ])
        );
        $this->app->instance('dompdf.wrapper', $pdfMock);

        $response = $this->actingAs($this->admin())
            ->get(route('analytics.export.pdf', ['from' => $this->from, 'to' => $this->to]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    // ── 16. Edge cases ─────────────────────────────────────────────────────

    /** @test */
    public function analytics_page_renders_with_completely_empty_database(): void
    {
        // No data at all — every section should gracefully return zeros/nulls/empty arrays
        $response = $this->actingAs($this->admin())->get($this->url());

        $response->assertOk();
        $this->assertEquals(0.0,   $response->viewData('totalGoldSmelted'));
        $this->assertEquals(0.0,   $response->viewData('totalOreMilled'));
        $this->assertNull($response->viewData('avgMillRecovery'));
        $this->assertNull($response->viewData('avgAisc'));
        $this->assertNull($response->viewData('avgCostPerTonne'));
        $this->assertEmpty($response->viewData('blastLabels'));
        $this->assertEmpty($response->viewData('drillLabels'));
        $this->assertEmpty($response->viewData('anomalies'));
    }

    /** @test */
    public function production_records_outside_date_range_do_not_affect_totals(): void
    {
        // Only outside-range row
        DailyProduction::create($this->prod(['date' => '2025-06-15', 'ore_milled' => 160, 'gold_smelted' => 30]));

        $response = $this->actingAs($this->admin())->get($this->url());

        $this->assertEquals(0.0, $response->viewData('totalGoldSmelted'));
        $this->assertEquals(0.0, $response->viewData('totalOreMilled'));
    }

    /** @test */
    public function total_absence_is_sum_of_sick_leave_and_awol(): void
    {
        SheIndicator::create(['date' => '2026-01-15', 'mining_department_id' => $this->dept()->id, 'lti' => 0, 'nlti' => 0, 'fatal_incident' => 0, 'medical_injury_case' => 0, 'sick' => 4, 'leave' => 3, 'awol' => 2]);

        $response = $this->actingAs($this->admin())->get($this->url());
        $this->assertEquals(9, $response->viewData('totalAbsence'));
    }
}
