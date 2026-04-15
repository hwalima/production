<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chemical extends Model
{
    use HasFactory;

    protected $fillable = [
        'sodium_cyanide',
        'lime',
        'caustic_soda',
        'iodised_salt',
        'mercury',
        'steel_balls',
        'hydrogen_peroxide',
        'borax',
        'nitric_acid',
        'sulphuric_acid',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'sodium_cyanide' => 'decimal:2',
        'lime' => 'decimal:2',
        'caustic_soda' => 'decimal:2',
        'iodised_salt' => 'decimal:2',
        'mercury' => 'decimal:2',
        'steel_balls' => 'decimal:2',
        'hydrogen_peroxide' => 'decimal:2',
        'borax' => 'decimal:2',
        'nitric_acid' => 'decimal:2',
        'sulphuric_acid' => 'decimal:2',
    ];
}
