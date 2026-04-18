<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBaseArticle;
use App\Models\KnowledgeBaseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KnowledgeBaseAdminController extends Controller
{
    // ═══════════════════════════ OVERVIEW ════════════════════════════════════

    public function index()
    {
        $categories = KnowledgeBaseCategory::withCount('articles')
            ->orderBy('sort_order')
            ->get();

        return view('knowledge-base.admin.index', compact('categories'));
    }

    // ═══════════════════════ CATEGORIES CRUD ════════════════════════════════

    public function createCategory()
    {
        return view('knowledge-base.admin.category-form', ['category' => null]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:120',
            'icon'       => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        KnowledgeBaseCategory::create([
            'title'      => $data['title'],
            'slug'       => $this->uniqueCategorySlug(Str::slug($data['title'])),
            'icon'       => $data['icon'] ?? '📄',
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()->route('kb.admin.index')->with('success', 'Category created.');
    }

    public function editCategory(KnowledgeBaseCategory $category)
    {
        return view('knowledge-base.admin.category-form', compact('category'));
    }

    public function updateCategory(Request $request, KnowledgeBaseCategory $category)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:120',
            'icon'       => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category->update([
            'title'      => $data['title'],
            'icon'       => $data['icon'] ?? $category->icon,
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
        ]);

        return redirect()->route('kb.admin.index')->with('success', 'Category updated.');
    }

    public function destroyCategory(KnowledgeBaseCategory $category)
    {
        $category->delete(); // cascades to articles
        return redirect()->route('kb.admin.index')->with('success', 'Category and its articles deleted.');
    }

    // ════════════════════════ ARTICLES CRUD ══════════════════════════════════

    public function createArticle(Request $request)
    {
        $categories = KnowledgeBaseCategory::orderBy('sort_order')->get();
        $selected   = $request->integer('category_id');
        return view('knowledge-base.admin.article-form', [
            'article'    => null,
            'categories' => $categories,
            'selected'   => $selected,
        ]);
    }

    public function storeArticle(Request $request)
    {
        $data = $request->validate([
            'knowledge_base_category_id' => 'required|exists:knowledge_base_categories,id',
            'title'        => 'required|string|max:200',
            'content'      => 'required|string',
            'sort_order'   => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
        ]);

        $slug = $this->uniqueArticleSlug(
            (int) $data['knowledge_base_category_id'],
            Str::slug($data['title'])
        );

        KnowledgeBaseArticle::create([
            'knowledge_base_category_id' => $data['knowledge_base_category_id'],
            'title'        => $data['title'],
            'slug'         => $slug,
            'content'      => $data['content'],
            'sort_order'   => $data['sort_order'] ?? 0,
            'is_published' => isset($data['is_published']) ? (bool) $data['is_published'] : true,
        ]);

        return redirect()->route('kb.admin.index')->with('success', 'Article created.');
    }

    public function editArticle(KnowledgeBaseArticle $article)
    {
        $categories = KnowledgeBaseCategory::orderBy('sort_order')->get();
        return view('knowledge-base.admin.article-form', compact('article', 'categories'));
    }

    public function updateArticle(Request $request, KnowledgeBaseArticle $article)
    {
        $data = $request->validate([
            'knowledge_base_category_id' => 'required|exists:knowledge_base_categories,id',
            'title'        => 'required|string|max:200',
            'content'      => 'required|string',
            'sort_order'   => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
        ]);

        $article->update([
            'knowledge_base_category_id' => $data['knowledge_base_category_id'],
            'title'        => $data['title'],
            'content'      => $data['content'],
            'sort_order'   => $data['sort_order'] ?? $article->sort_order,
            'is_published' => isset($data['is_published']) ? (bool) $data['is_published'] : false,
        ]);

        return redirect()->route('kb.admin.index')->with('success', 'Article updated.');
    }

    public function destroyArticle(KnowledgeBaseArticle $article)
    {
        $article->delete();
        return redirect()->route('kb.admin.index')->with('success', 'Article deleted.');
    }

    public function toggleArticle(KnowledgeBaseArticle $article)
    {
        $article->update(['is_published' => ! $article->is_published]);
        return back()->with('success', $article->is_published ? 'Article published.' : 'Article unpublished.');
    }

    // ════════════════════════ HELPERS ════════════════════════════════════════

    private function uniqueCategorySlug(string $base): string
    {
        $slug = $base;
        $i    = 2;
        while (KnowledgeBaseCategory::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }

    private function uniqueArticleSlug(int $categoryId, string $base): string
    {
        $slug = $base;
        $i    = 2;
        while (KnowledgeBaseArticle::where('knowledge_base_category_id', $categoryId)
            ->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }
}
