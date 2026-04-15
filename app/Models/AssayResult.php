<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssayResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'date',
        'description',
        'assay_value',
        'daily_production_id',
    ];

    protected $casts = [
        'date' => 'date',
        'assay_value' => 'decimal:4',
    ];

    public function dailyProduction()
    {
        return $this->belongsTo(DailyProduction::class);
    }
}
