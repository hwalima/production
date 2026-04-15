<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrillingRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'end_name',
        'hole_count',
        'drill_steel_length',
        'advance',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'drill_steel_length' => 'decimal:2',
        'advance' => 'decimal:2',
    ];
}
