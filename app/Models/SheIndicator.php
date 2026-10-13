<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SheIndicator extends Model
{
    protected $fillable = [
        'date', 'mining_department_id',
        'medical_injury_case', 'fatal_incident', 'lti', 'nlti', 'iod',
        'environmental_incidents_penalties',
        'medical_injury_case_description', 'fatal_incident_description',
        'lti_description', 'nlti_description', 'iod_description',
        'environmental_incidents_penalties_description',
    ];

    protected $casts = ['date' => 'date'];

    public function department()
    {
        return $this->belongsTo(MiningDepartment::class, 'mining_department_id');
    }
}
