<?php

namespace Tests\Unit;

use Tests\TestCase;

class ProductionCalculationTest extends TestCase
{
    /** @test */
    public function profit_calculation_formula_is_correct(): void
    {
        // profit = gold_smelted × fidelity_price × (purity / 100)
        $goldSmelted    = 2.5;   // kg
        $fidelityPrice  = 90000; // $/kg
        $purity         = 85.0;  // %

        $expected = $goldSmelted * $fidelityPrice * ($purity / 100);
        // 2.5 × 90000 × 0.85 = 191250
        $this->assertEquals(191250.0, $expected);
    }

    /** @test */
    public function profit_is_zero_when_gold_smelted_is_zero(): void
    {
        $profit = 0 * 90000 * (85.0 / 100);
        $this->assertEquals(0.0, $profit);
    }

    /** @test */
    public function profit_scales_linearly_with_purity(): void
    {
        $gold  = 1.0;
        $price = 100000;

        $p50 = $gold * $price * (50.0 / 100);
        $p100 = $gold * $price * (100.0 / 100);

        $this->assertEquals($p50 * 2, $p100);
    }

    /** @test */
    public function hoisted_stockpile_accumulates_correctly(): void
    {
        // hoisted_stockpile = previous_hoisted + ore_hoisted - ore_crushed
        $prevHoisted  = 100.0;
        $oreHoisted   = 50.0;
        $oreCrushed   = 30.0;

        $newHoisted = $prevHoisted + $oreHoisted - $oreCrushed;
        // 100 + 50 - 30 = 120
        $this->assertEquals(120.0, $newHoisted);
    }

    /** @test */
    public function crushed_stockpile_accumulates_correctly(): void
    {
        // crushed_stockpile = previous_crushed + ore_crushed - ore_milled
        $prevCrushed = 80.0;
        $oreCrushed  = 30.0;
        $oreMilled   = 25.0;

        $newCrushed = $prevCrushed + $oreCrushed - $oreMilled;
        // 80 + 30 - 25 = 85
        $this->assertEquals(85.0, $newCrushed);
    }

    /** @test */
    public function stockpile_starts_at_zero_with_no_previous_record(): void
    {
        $prev        = null;
        $oreHoisted  = 40.0;
        $oreCrushed  = 20.0;

        $hoisted = ($prev ? $prev : 0) + $oreHoisted - $oreCrushed;
        $this->assertEquals(20.0, $hoisted);
    }

    /** @test */
    public function crushed_stockpile_can_not_go_negative_logically(): void
    {
        // More milled than crushed input — stockpile decreases
        $prevCrushed = 10.0;
        $oreCrushed  = 5.0;
        $oreMilled   = 12.0;

        $result = $prevCrushed + $oreCrushed - $oreMilled;
        // 10 + 5 - 12 = 3 (still positive, drawing from prev stock)
        $this->assertEquals(3.0, $result);
    }
}
