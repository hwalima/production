<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabourDeptCost extends Model
{
    protected $table = 'labour_dept_costs';

    protected $fillable = [
        'labour_energy_id',
        'mining_department_id',
        'labour_cost',
    ];

    protected $casts = [
        'labour_cost' => 'decimal:2',
    ];

    public function labourEnergy()
    {
        return $this->belongsTo(LabourEnergy::class);
    }

    public function department()
    {
        return $this->belongsTo(MiningDepartment::class, 'mining_department_id');
    }
}
