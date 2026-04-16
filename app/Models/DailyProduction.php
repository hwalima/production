<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyProduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'shift',
        'mining_site',
        'ore_hoisted',
        'ore_hoisted_target',
        'waste_hoisted',
        'uncrushed_stockpile',
        'ore_crushed',
        'unmilled_stockpile',
        'ore_milled',
        'ore_milled_target',
        'gold_smelted',
        'purity_percentage',
        'fidelity_price',
    ];

    protected $casts = [
        'date'                 => 'date',
        'ore_hoisted'          => 'decimal:2',
        'ore_hoisted_target'   => 'decimal:2',
        'waste_hoisted'        => 'decimal:2',
        'uncrushed_stockpile'  => 'decimal:2',
        'ore_crushed'          => 'decimal:2',
        'unmilled_stockpile'   => 'decimal:2',
        'ore_milled'           => 'decimal:2',
        'ore_milled_target'    => 'decimal:2',
        'gold_smelted'         => 'decimal:2',
        'purity_percentage'    => 'decimal:2',
        'fidelity_price'       => 'decimal:2',
    ];

    public function assayResults()
    {
        return $this->hasMany(AssayResult::class);
    }
}
