<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SheRequirementEntry extends Model
{
    protected $fillable = ['she_requirement_item_id', 'period', 'unit_value', 'notes'];

    protected $casts = ['period' => 'date'];

    public function item()
    {
        return $this->belongsTo(SheRequirementItem::class);
    }
}
