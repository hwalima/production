<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeBaseArticle extends Model
{
    protected $fillable = [
        'knowledge_base_category_id', 'title', 'slug', 'content', 'sort_order', 'is_published',
    ];

    protected $casts = ['is_published' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseCategory::class, 'knowledge_base_category_id');
    }
}
