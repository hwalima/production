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
        'mining_cost',
        'milling_cost',
        'maintenance_cost',
        'transport_cost',
        'leave',
        'offdays',
        'sick',
        'awol',
        'terminations',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'zesa_cost' => 'decimal:2',
        'diesel_cost' => 'decimal:2',
        'labour_cost' => 'decimal:2',
        'mining_cost' => 'decimal:2',
        'milling_cost' => 'decimal:2',
        'maintenance_cost' => 'decimal:2',
        'transport_cost' => 'decimal:2',
        'leave' => 'decimal:2',
        'offdays' => 'decimal:2',
        'sick' => 'decimal:2',
        'awol' => 'decimal:2',
        'terminations' => 'decimal:2',
    ];

    public function deptCosts()
    {
        return $this->hasMany(LabourDeptCost::class, 'labour_energy_id');
    }

    /**
     * Recalculate the cached labour_cost total from dept costs and persist it.
     */
    public function syncLabourTotal(): void
    {
        $total = $this->deptCosts()->sum('labour_cost');
        $this->update(['labour_cost' => $total]);
    }
}
