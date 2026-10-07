<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DailyProductionFactory extends Factory
{
    public function definition(): array
    {
        $milled = $this->faker->randomFloat(2, 30, 200);

        return [
            'date'                 => $this->faker->unique()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'shift'                => null,
            'mining_site'          => null,
            'ore_hoisted'          => 0,
            'ore_hoisted_target'   => null,
            'waste_hoisted'        => 0,
            'uncrushed_stockpile'  => 0,
            'ore_crushed'          => $this->faker->randomFloat(2, 40, 250),
            'unmilled_stockpile'   => 0,
            'ore_milled'           => $milled,
            'ro_mine_milled'       => 0,
            'sanda_milled'         => $milled,
            'sanda_milled_manual'  => false,
            'ore_milled_target'    => $this->faker->randomFloat(2, 30, 200),
            'gold_smelted'         => $this->faker->randomFloat(2, 0.5, 10),
            'purity_percentage'    => $this->faker->randomFloat(2, 70, 99),
            'fidelity_price'       => $this->faker->randomFloat(2, 80, 120),

        ];
    }
}
