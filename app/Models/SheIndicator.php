<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SheIndicator extends Model
{
    protected $fillable = [
        'date', 'mining_department_id',
        'medical_injury_case', 'fatal_incident', 'lti', 'nlti',
        'leave', 'offdays', 'sick', 'iod', 'awol', 'terminations',
    ];

    protected $casts = ['date' => 'date'];

    public function department()
    {
        return $this->belongsTo(MiningDepartment::class, 'mining_department_id');
    }
}
