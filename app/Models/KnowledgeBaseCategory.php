<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeBaseCategory extends Model
{
    protected $fillable = ['title', 'slug', 'icon', 'sort_order'];

    public function articles(): HasMany
    {
        return $this->hasMany(KnowledgeBaseArticle::class)->orderBy('sort_order');
    }

    public function publishedArticles(): HasMany
    {
        return $this->articles()->where('is_published', true);
    }
}
