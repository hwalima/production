<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabourEnergy extends Model
{
    use HasFactory;

    protected $table = 'labour_energy';

    protected $fillable = [
        'zesa_cost',
        'diesel_cost',
        'labour_cost',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'zesa_cost' => 'decimal:2',
        'diesel_cost' => 'decimal:2',
        'labour_cost' => 'decimal:2',
    ];
}
