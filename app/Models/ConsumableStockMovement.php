<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumableStockMovement extends Model
{
    protected $fillable = [
        'consumable_id', 'user_id', 'type', 'direction',
        'quantity', 'packs', 'unit_cost', 'total_cost',
        'movement_date', 'reference', 'notes',
    ];

    protected $casts = [
        'movement_date' => 'date',
        'quantity'      => 'decimal:4',
        'packs'         => 'decimal:4',
        'unit_cost'     => 'decimal:4',
        'total_cost'    => 'decimal:2',
    ];

    public function consumable(): BelongsTo
    {
        return $this->belongsTo(Consumable::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'purchase'   => 'Purchase',
            'usage'      => 'Usage',
            'adjustment' => 'Adjustment',
            'return'     => 'Return',
            default      => ucfirst($this->type),
        };
    }
}
