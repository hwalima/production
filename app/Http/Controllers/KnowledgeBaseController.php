<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBaseArticle;
use App\Models\KnowledgeBaseCategory;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    /** Load all categories with published articles — reused across views. */
    private function allCategories()
    {
        return KnowledgeBaseCategory::with([
            'articles' => fn ($q) => $q->where('is_published', true)->orderBy('sort_order'),
        ])->orderBy('sort_order')->get();
    }

    /** /help  → redirect to the first article, or show empty state. */
    public function index()
    {
        $categories = $this->allCategories();
        $first = $categories->first()?->articles->first();

        if ($first) {
            return redirect()->route('kb.show', [
                $categories->first()->slug,
                $first->slug,
            ]);
        }

        return view('knowledge-base.show', [
            'categories' => $categories,
            'category'   => null,
            'article'    => null,
            'prev'       => null,
            'next'       => null,
        ]);
    }

    /** /help/{category}/{article} */
    public function show(string $categorySlug, string $articleSlug)
    {
        $categories = $this->allCategories();

        $category = $categories->firstWhere('slug', $categorySlug);
        abort_if(! $category, 404);

        $article = $category->articles->firstWhere('slug', $articleSlug);
        abort_if(! $article, 404);

        // Build a flat list across all categories for prev/next navigation
        $allArticles = $categories->flatMap(function ($cat) {
            return $cat->articles->map(function ($art) use ($cat) {
                $art->setRelation('category', $cat);
                return $art;
            });
        });

        $idx  = $allArticles->search(fn ($a) => $a->id === $article->id);
        $prev = $idx > 0 ? $allArticles->get($idx - 1) : null;
        $next = $idx < $allArticles->count() - 1 ? $allArticles->get($idx + 1) : null;

        return view('knowledge-base.show', compact('categories', 'category', 'article', 'prev', 'next'));
    }

    /** /help/search?q=... */
    public function search(Request $request)
    {
        $q       = trim($request->input('q', ''));
        $results = collect();

        if (mb_strlen($q) >= 2) {
            $results = KnowledgeBaseArticle::with('category')
                ->where('is_published', true)
                ->where(function ($query) use ($q) {
                    $query->where('title', 'like', "%{$q}%")
                          ->orWhere('content', 'like', "%{$q}%");
                })
                ->orderBy('knowledge_base_category_id')
                ->orderBy('sort_order')
                ->get();
        }

        $categories = $this->allCategories();

        return view('knowledge-base.search', compact('results', 'q', 'categories'));
    }
}
