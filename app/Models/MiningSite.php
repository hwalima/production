<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiningSite extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
