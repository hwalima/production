<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Consumable extends Model
{
    protected $fillable = [
        'name', 'category', 'description',
        'purchase_unit', 'use_unit',
        'units_per_pack', 'pack_cost', 'reorder_level', 'is_active',
    ];

    protected $casts = [
        'units_per_pack' => 'decimal:4',
        'pack_cost'      => 'decimal:2',
        'reorder_level'  => 'decimal:4',
        'is_active'      => 'boolean',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(ConsumableStockMovement::class);
    }

    /** Cost per single use_unit */
    public function getUnitCostAttribute(): float
    {
        $upp = (float) $this->units_per_pack;
        return $upp > 0 ? (float) $this->pack_cost / $upp : 0;
    }

    /** Current stock level in use_units (in - out across all movements) */
    public function getCurrentStockAttribute(): float
    {
        $in  = $this->movements()->where('direction', 'in')->sum('quantity');
        $out = $this->movements()->where('direction', 'out')->sum('quantity');
        return (float) $in - (float) $out;
    }

    public function getLowStockAttribute(): bool
    {
        $level = (float) $this->reorder_level;
        return $level > 0 && $this->current_stock <= $level;
    }

    public static function categoryLabel(string $category): string
    {
        return match($category) {
            'blasting'   => '🧨 Blasting',
            'chemicals'  => '⚗️ Chemicals',
            'mechanical' => '🔧 Mechanical',
            'ppe'        => '🦺 PPE',
            'general'    => '📦 General',
            default      => ucfirst($category),
        };
    }

    public static function categoryColor(string $category): string
    {
        return match($category) {
            'blasting'   => '#ef4444',
            'chemicals'  => '#8b5cf6',
            'mechanical' => '#6b7280',
            'ppe'        => '#22c55e',
            'general'    => '#fcb913',
            default      => '#9ca3af',
        };
    }

    public static function categoryBg(string $category): string
    {
        return match($category) {
            'blasting'   => 'rgba(239,68,68,.12)',
            'chemicals'  => 'rgba(139,92,246,.12)',
            'mechanical' => 'rgba(107,114,128,.12)',
            'ppe'        => 'rgba(34,197,94,.12)',
            'general'    => 'rgba(252,185,19,.12)',
            default      => 'rgba(156,163,175,.12)',
        };
    }
}
