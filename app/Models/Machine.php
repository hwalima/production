<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Machine extends Model
{
    protected $fillable = [
        'machine_code',
        'description',
        'service_interval_hours',
        'service_tracking_started_at',
        'service_alert_sent_at',
        'is_active',
    ];

    protected $casts = [
        'service_interval_hours' => 'integer',
        'service_tracking_started_at' => 'datetime',
        'service_alert_sent_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function runtimes(): HasMany
    {
        return $this->hasMany(MachineRuntime::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(MachineService::class);
    }

    public function latestRuntime(): HasOne
    {
        return $this->hasOne(MachineRuntime::class)->latestOfMany('end_time');
    }

    public function latestService(): HasOne
    {
        return $this->hasOne(MachineService::class)->latestOfMany('serviced_at');
    }

    public function hoursSinceLastService(): float
    {
        $since = $this->latestService?->serviced_at ?? $this->service_tracking_started_at;
        $runtimes = $this->runtimes();

        if ($since) {
            $runtimes->where('start_time', '>=', $since);
        }

        return (float) $runtimes->sum('hours_run');
    }

    public function serviceHoursRemaining(): float
    {
        return max(0, $this->service_interval_hours - $this->hoursSinceLastService());
    }

    public function isServiceDue(?float $hoursSinceService = null): bool
    {
        return $this->latestRuntime !== null
            && ($hoursSinceService ?? $this->hoursSinceLastService()) >= $this->service_interval_hours;
    }

    public function isServiceDueSoon(?float $hoursSinceService = null): bool
    {
        $hoursSinceService ??= $this->hoursSinceLastService();
        $remaining = max(0, $this->service_interval_hours - $hoursSinceService);

        return $this->latestRuntime !== null
            && !$this->isServiceDue($hoursSinceService)
            && $remaining <= min(24, $this->service_interval_hours * 0.1);
    }
}
