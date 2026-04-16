<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SheRequirementItem extends Model
{
    protected $fillable = ['category', 'name', 'unit_of_measure', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function entries()
    {
        return $this->hasMany(SheRequirementEntry::class);
    }
}
