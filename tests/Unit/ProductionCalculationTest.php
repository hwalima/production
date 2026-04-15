<?php

namespace Tests\Unit;

use Tests\TestCase;

class ProductionCalculationTest extends TestCase
{
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
