<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SheIndicator extends Model
{
    protected $fillable = [
        'period', 'department',
        'medical_injury_case', 'fatal_incident', 'lti', 'nlti',
        'leave', 'offdays', 'sick', 'iod', 'awol', 'terminations',
    ];

    protected $casts = ['period' => 'date'];
}
