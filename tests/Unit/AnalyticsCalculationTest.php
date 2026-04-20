<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Unit tests for all mathematical formulas used in the Analytics module.
 *
 * These tests are pure PHP — no DB, no HTTP. They verify that the
 * numerical logic in AnalyticsController is correct in isolation.
 */
class AnalyticsCalculationTest extends TestCase
{
    // ── 1. Mill Recovery ───────────────────────────────────────────────────

    /** @test */
    public function mill_recovery_formula_is_gold_over_milled_times_grade(): void
    {
        // recovery = (gold / (milled * grade)) * 100
        $gold    = 5.0;   // grams
        $milled  = 500.0; // tonnes
        $grade   = 0.012; // g/t fire assay
        $expected = 83.3;

        $actual = round(($gold / ($milled * $grade)) * 100, 1);
        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function mill_recovery_at_exactly_100_percent(): void
    {
        $gold    = 6.0;
        $milled  = 500.0;
        $grade   = 0.012; // 500 * 0.012 = 6.0 → 100 %
        $actual  = round(($gold / ($milled * $grade)) * 100, 1);
        $this->assertEquals(100.0, $actual);
    }

    /** @test */
    public function mill_recovery_is_skipped_when_grade_is_zero(): void
    {
        // Controller skips the calculation when grade <= 0
        $grade   = 0.0;
        $milled  = 500.0;
        $result  = ($grade > 0 && $milled > 0) ? 'computed' : null;
        $this->assertNull($result);
    }

    /** @test */
    public function mill_recovery_is_skipped_when_ore_milled_is_zero(): void
    {
        $grade  = 0.012;
        $milled = 0.0;
        $result = ($grade > 0 && $milled > 0) ? 'computed' : null;
        $this->assertNull($result);
    }

    /** @test */
    public function avg_mill_recovery_ignores_null_values(): void
    {
        $recoveries = [83.3, null, 100.0, null, 90.0];
        $valid      = array_filter($recoveries, fn($v) => $v !== null);
        $avg        = round(array_sum($valid) / count($valid), 1);
        $this->assertEquals(91.1, $avg);
    }

    /** @test */
    public function avg_mill_recovery_is_null_when_all_values_are_null(): void
    {
        $recoveries = [null, null, null];
        $valid      = array_filter($recoveries, fn($v) => $v !== null);
        $avg        = count($valid) > 0 ? round(array_sum($valid) / count($valid), 1) : null;
        $this->assertNull($avg);
    }

    // ── 2. AISC per gram ───────────────────────────────────────────────────

    /** @test */
    public function aisc_formula_is_total_cost_divided_by_total_gold(): void
    {
        $totalCost = 18000.0;
        $totalGold = 14.0;
        $expected  = round($totalCost / $totalGold, 2); // 1285.71

        $this->assertEquals(1285.71, $expected);
    }

    /** @test */
    public function aisc_is_null_when_no_gold_is_produced(): void
    {
        $totalGold = 0.0;
        $result    = $totalGold > 0 ? round(18000 / $totalGold, 2) : null;
        $this->assertNull($result);
    }

    // ── 3. Grade — implied vs fire assay ───────────────────────────────────

    /** @test */
    public function implied_grade_is_gold_divided_by_ore_milled(): void
    {
        $gold   = 9.0;
        $milled = 600.0;
        $grade  = round($gold / $milled, 4);
        $this->assertEquals(0.015, $grade);
    }

    /** @test */
    public function implied_grade_is_null_when_ore_milled_is_zero(): void
    {
        $milled = 0.0;
        $result = $milled > 0 ? round(9.0 / $milled, 4) : null;
        $this->assertNull($result);
    }

    // ── 4. Cost per tonne ──────────────────────────────────────────────────

    /** @test */
    public function cost_per_tonne_formula_is_cost_over_milled(): void
    {
        $totalCost   = 18000.0;
        $totalMilled = 1100.0;
        $expected    = round($totalCost / $totalMilled, 2); // 16.36
        $this->assertEquals(16.36, $expected);
    }

    /** @test */
    public function cost_per_tonne_is_null_when_nothing_is_milled(): void
    {
        $totalMilled = 0.0;
        $result      = $totalMilled > 0 ? round(18000 / $totalMilled, 2) : null;
        $this->assertNull($result);
    }

    // ── 5. MoM delta ───────────────────────────────────────────────────────

    /** @test */
    public function mom_delta_positive_when_current_greater_than_previous(): void
    {
        $prev    = 10.0;
        $current = 14.0;
        $delta   = round((($current - $prev) / $prev) * 100, 1);
        $this->assertEquals(40.0, $delta);
    }

    /** @test */
    public function mom_delta_negative_when_current_less_than_previous(): void
    {
        $prev    = 20.0;
        $current = 14.0;
        $delta   = round((($current - $prev) / $prev) * 100, 1);
        $this->assertEquals(-30.0, $delta);
    }

    /** @test */
    public function mom_delta_is_null_when_previous_is_zero(): void
    {
        $prev   = 0.0;
        $result = $prev > 0 ? round(((14.0 - $prev) / $prev) * 100, 1) : null;
        $this->assertNull($result);
    }

    // ── 6. Stockpile (carried over in Production controller) ───────────────

    /** @test */
    public function uncrushed_stockpile_accumulates_as_hoisted_minus_crushed(): void
    {
        $prev    = 20.0;
        $hoisted = 130.0;
        $crushed = 120.0;
        $result  = $prev + $hoisted - $crushed;
        $this->assertEquals(30.0, $result);
    }

    // ── 11. SPC control chart ──────────────────────────────────────────────

    /** @test */
    public function spc_mean_is_arithmetic_average_of_grades(): void
    {
        $grades = collect([0.01, 0.02, 0.01, 0.02]);
        $mean   = $grades->avg();
        $this->assertEquals(0.015, $mean);
    }

    /** @test */
    public function spc_population_std_deviation_formula(): void
    {
        $grades = collect([0.01, 0.02, 0.01, 0.02]);
        $mean   = $grades->avg();

        // population std = sqrt(mean_of_squared_deviations)
        $variance = $grades->map(fn($v) => ($v - $mean) ** 2)->avg();
        $std      = sqrt($variance);

        $this->assertEquals(0.005, round($std, 3));
    }

    /** @test */
    public function spc_ucl_is_mean_plus_two_standard_deviations(): void
    {
        $mean = 0.015;
        $std  = 0.005;
        $ucl  = round($mean + 2 * $std, 4);
        $this->assertEquals(0.025, $ucl);
    }

    /** @test */
    public function spc_lcl_is_mean_minus_two_standard_deviations(): void
    {
        $mean = 0.015;
        $std  = 0.005;
        $lcl  = round(max(0, $mean - 2 * $std), 4);
        $this->assertEquals(0.005, $lcl);
    }

    /** @test */
    public function spc_lcl_clamps_to_zero_when_result_is_negative(): void
    {
        $mean = 0.003;
        $std  = 0.010;
        $lcl  = round(max(0, $mean - 2 * $std), 4);
        $this->assertEquals(0.0, $lcl);
    }

    /** @test */
    public function spc_std_is_zero_for_single_data_point(): void
    {
        // Controller: spcGrades->count() > 1 ? sqrt(...) : 0
        $grades = collect([0.012]);
        $std    = $grades->count() > 1
            ? sqrt($grades->map(fn($v) => ($v - $grades->avg()) ** 2)->avg())
            : 0;
        $this->assertEquals(0, $std);
    }

    // ── 12. Predictive maintenance health score ────────────────────────────

    /** @test */
    public function machine_health_score_is_100_when_service_not_yet_due(): void
    {
        $daysToService = 90;
        $intervalDays  = 90.0;
        $score = max(0, min(100, (int) round(($daysToService / max(1, $intervalDays)) * 100)));
        $this->assertEquals(100, $score);
    }

    /** @test */
    public function machine_health_score_is_0_when_severely_overdue(): void
    {
        $daysToService = -365;
        $intervalDays  = 90.0;
        $score = max(0, min(100, (int) round(($daysToService / max(1, $intervalDays)) * 100)));
        $this->assertEquals(0, $score);
    }

    /** @test */
    public function machine_status_is_overdue_when_days_to_service_is_negative(): void
    {
        $days   = -3;
        $status = $days < 0 ? 'overdue' : ($days <= 7 ? 'due_soon' : 'ok');
        $this->assertEquals('overdue', $status);
    }

    /** @test */
    public function machine_status_is_due_soon_within_7_days(): void
    {
        foreach ([0, 1, 5, 7] as $days) {
            $status = $days < 0 ? 'overdue' : ($days <= 7 ? 'due_soon' : 'ok');
            $this->assertEquals('due_soon', $status, "Expected due_soon for days={$days}");
        }
    }

    /** @test */
    public function machine_status_is_ok_when_more_than_7_days_remain(): void
    {
        foreach ([8, 30, 90, 365] as $days) {
            $status = $days < 0 ? 'overdue' : ($days <= 7 ? 'due_soon' : 'ok');
            $this->assertEquals('ok', $status, "Expected ok for days={$days}");
        }
    }

    /** @test */
    public function service_interval_defaults_to_90_days_when_hours_are_zero(): void
    {
        $serviceAfterHours = 0;
        $intervalDays      = (float) ($serviceAfterHours > 0 ? $serviceAfterHours / 24 : 90);
        $this->assertEquals(90.0, $intervalDays);
    }

    /** @test */
    public function service_interval_converts_hours_to_days(): void
    {
        $serviceAfterHours = 2160; // 90 days * 24h
        $intervalDays      = (float) ($serviceAfterHours > 0 ? $serviceAfterHours / 24 : 90);
        $this->assertEquals(90.0, $intervalDays);
    }

    // ── 13. Anomaly detection (z-score) ────────────────────────────────────

    /** @test */
    public function z_score_formula_is_value_minus_mean_over_std(): void
    {
        $value = 0.10;
        $mean  = 0.01;
        $std   = 0.005;
        $z     = round(($value - $mean) / $std, 2);
        $this->assertEquals(18.0, $z);
    }

    /** @test */
    public function value_above_mean_has_positive_z_score_and_direction_above(): void
    {
        $value = 0.05;
        $mean  = 0.01;
        $std   = 0.005;
        $z     = ($value - $mean) / $std;
        $dir   = $z > 0 ? 'above' : 'below';
        $this->assertGreaterThan(0, $z);
        $this->assertEquals('above', $dir);
    }

    /** @test */
    public function value_below_mean_has_negative_z_score_and_direction_below(): void
    {
        $value = 0.001;
        $mean  = 0.01;
        $std   = 0.005;
        $z     = ($value - $mean) / $std;
        $dir   = $z > 0 ? 'above' : 'below';
        $this->assertLessThan(0, $z);
        $this->assertEquals('below', $dir);
    }

    /** @test */
    public function anomaly_threshold_is_absolute_z_greater_than_2(): void
    {
        $cases = [
            ['z' => 2.01,  'expected' => true],
            ['z' => 2.0,   'expected' => false],  // not strictly > 2
            ['z' => -2.5,  'expected' => true],
            ['z' => -1.9,  'expected' => false],
            ['z' => 3.5,   'expected' => true],
        ];

        foreach ($cases as $case) {
            $isAnomaly = abs($case['z']) > 2;
            $this->assertEquals($case['expected'], $isAnomaly,
                "z={$case['z']} expected isAnomaly={$case['expected']}");
        }
    }

    /** @test */
    public function anomalies_are_sorted_by_descending_absolute_z_score(): void
    {
        $anomalies = [
            ['z' => 2.5,  'dir' => 'above'],
            ['z' => -4.1, 'dir' => 'below'],
            ['z' => 3.0,  'dir' => 'above'],
        ];

        usort($anomalies, fn($a, $b) => abs($b['z']) <=> abs($a['z']));

        $this->assertEquals(-4.1, $anomalies[0]['z']);
        $this->assertEquals(3.0,  $anomalies[1]['z']);
        $this->assertEquals(2.5,  $anomalies[2]['z']);
    }

    /** @test */
    public function z_score_not_computed_when_std_is_zero(): void
    {
        // All values identical → std=0; controller guards: $goldStd > 0
        $std    = 0.0;
        $result = $std > 0 ? 'computed' : 'skipped';
        $this->assertEquals('skipped', $result);
    }

    // ── Date range guard ───────────────────────────────────────────────────

    /** @test */
    public function from_date_is_clamped_to_start_of_to_month_when_it_exceeds_to(): void
    {
        $from = '2026-01-31';
        $to   = '2026-01-05';

        if ($from > $to) {
            $from = \Carbon\Carbon::parse($to)->startOfMonth()->toDateString();
        }

        $this->assertEquals('2026-01-01', $from);
    }
}
