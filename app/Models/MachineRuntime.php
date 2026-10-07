<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineRuntime extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'machine_code',
        'description',
        'start_time',
        'end_time',
        'hours_run',
        'service_after_hours',
        'next_service_date',
        'service_alert_sent_at',
    ];

    protected $casts = [
        'machine_id'           => 'integer',
        'start_time'           => 'datetime',
        'end_time'             => 'datetime',
        'hours_run'             => 'float',
        'next_service_date'    => 'date',
        'service_alert_sent_at'=> 'datetime',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}
