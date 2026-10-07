<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiningRecord extends Model
{
    protected $fillable = [
        'date',
        'shift',
        'mining_site',
        'ore_hoisted_entered',
        'ore_hoisted',
        'ore_hoisted_target',
        'waste_hoisted',
        'skip_factor',
    ];

    protected $casts = [
        'date' => 'date',
        'ore_hoisted_entered' => 'decimal:2',
        'ore_hoisted' => 'decimal:2',
        'ore_hoisted_target' => 'decimal:2',
        'waste_hoisted' => 'decimal:2',
        'skip_factor' => 'decimal:4',
    ];
}
