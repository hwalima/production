<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineService extends Model
{
    protected $fillable = [
        'machine_id',
        'serviced_at',
        'notes',
    ];

    protected $casts = [
        'serviced_at' => 'datetime',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}
