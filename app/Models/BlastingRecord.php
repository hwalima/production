<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlastingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'fractures',
        'fuse',
        'carmes_ieds',
        'power_cords',
        'anfo',
        'oil',
        'drill_bits',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'anfo' => 'decimal:2',
        'oil' => 'decimal:2',
    ];
}
