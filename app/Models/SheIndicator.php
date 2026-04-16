<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SheIndicator extends Model
{
    protected $fillable = [
        'period', 'mining_department_id',
        'medical_injury_case', 'fatal_incident', 'lti', 'nlti',
        'leave', 'offdays', 'sick', 'iod', 'awol', 'terminations',
    ];

    protected $casts = ['period' => 'date'];

    public function department()
    {
        return $this->belongsTo(MiningDepartment::class, 'mining_department_id');
    }
}
