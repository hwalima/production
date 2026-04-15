<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineRuntime extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_code',
        'description',
        'start_time',
        'end_time',
        'service_after_hours',
        'next_service_date',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'next_service_date' => 'date',
    ];
}
