@extends('layouts.app')
@section('page-title', 'Manage Knowledge Base')

@section('content')
<style>
.kb-admin-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:16px; margin-top:20px; }
.kb-admin-cat-card { background:var(--card); border:1px solid var(--topbar-border); border-radius:10px; overflow:hidden; }
.kb-admin-cat-card .card-head {
    padding:12px 16px; display:flex; align-items:center; gap:8px;
    border-bottom:1px solid var(--topbar-border);
    background:rgba(252,185,19,.05);
}
.kb-admin-cat-card .card-head .cat-icon { font-size:1.2rem; }
.kb-admin-cat-card .card-head .cat-title { font-weight:700; color:var(--text); flex:1; font-size:.92rem; }
.kb-admin-cat-card .card-head .cat-order { font-size:.72rem; color:#9ca3af; }
.kb-admin-cat-card .card-head .head-actions { display:flex; gap:6px; }
.kb-admin-cat-card .card-head .head-actions a,
.kb-admin-cat-card .card-head .head-actions button { padding:3px 9px; border-radius:5px; font-size:.75rem; cursor:pointer; text-decoration:none; border:1px solid; }
.kb-admin-cat-card .card-head .btn-edit-cat  { background:rgba(252,185,19,.12); border-color:rgba(252,185,19,.3); color:#fcb913; }
.kb-admin-cat-card .card-head .btn-del-cat   { background:rgba(239,68,68,.08); border-color:rgba(239,68,68,.3); color:#ef4444; }
.kb-admin-cat-card .card-body { padding:10px 0; }
.kb-admin-cat-card .art-row {
    display:flex; align-items:center; gap:8px;
    padding:7px 16px; border-bottom:1px solid var(--topbar-border);
    font-size:.83rem;
}
.kb-admin-cat-card .art-row:last-child { border-bottom:none; }
.kb-admin-cat-card .art-row .art-title { flex:1; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.kb-admin-cat-card .art-row .art-draft { font-size:.68rem; background:rgba(239,68,68,.12); color:#ef4444; border:1px solid rgba(239,68,68,.25); border-radius:99px; padding:1px 7px; }
.kb-admin-cat-card .art-row .art-actions { display:flex; gap:5px; flex-shrink:0; }
.kb-admin-cat-card .art-row .art-actions a,
.kb-admin-cat-card .art-row .art-actions button { padding:2px 8px; border-radius:4px; font-size:.73rem; cursor:pointer; text-decoration:none; border:1px solid; }
.btn-sm-edit    { background:rgba(252,185,19,.1);  border-color:rgba(252,185,19,.3); color:#fcb913;  }
.btn-sm-toggle  { background:rgba(52,211,153,.08); border-color:rgba(52,211,153,.3); color:#34d399; }
.btn-sm-untog   { background:rgba(239,68,68,.08);  border-color:rgba(239,68,68,.3); color:#ef4444;  }
.btn-sm-del     { background:rgba(239,68,68,.08);  border-color:rgba(239,68,68,.3); color:#ef4444;  }
.kb-admin-cat-card .add-art-row { padding:8px 16px; }
.kb-admin-cat-card .add-art-row a { font-size:.8rem; color:#fcb913; text-decoration:none; opacity:.8; }
.kb-admin-cat-card .add-art-row a:hover { opacity:1; }
.no-cats { text-align:center; padding:60px; color:#9ca3af; }
</style>

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
    <div>
        <h2 class="page-title">📚 Knowledge Base — Manage Content</h2>
        <p style="color:#9ca3af;font-size:.85rem;margin:4px 0 0;">
            {{ $categories->sum('articles_count') }} articles across {{ $categories->count() }} categories
        </p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <a href="{{ route('kb.index') }}" class="btn-cancel">View Public KB</a>
        <a href="{{ route('kb.categories.create') }}" class="btn-add">+ New Category</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3);border-radius:8px;padding:10px 16px;margin-bottom:16px;color:#34d399;font-size:.85rem;">
    ✅ {{ session('success') }}
</div>
@endif

@if($categories->isEmpty())
<div class="no-cats">
    <div style="font-size:2.5rem;margin-bottom:14px;">📂</div>
    <h3 style="color:var(--text);">No categories yet</h3>
    <p>Create your first category to start building the knowledge base.</p>
    <a href="{{ route('kb.categories.create') }}" class="btn-add" style="display:inline-block;margin-top:16px;">+ New Category</a>
</div>
@else
<div class="kb-admin-grid">
    @foreach($categories as $cat)
    <div class="kb-admin-cat-card">
        <div class="card-head">
            <span class="cat-icon">{{ $cat->icon }}</span>
            <span class="cat-title">{{ $cat->title }}</span>
            <span class="cat-order">#{{ $cat->sort_order }}</span>
            <div class="head-actions">
                <a href="{{ route('kb.categories.edit', $cat) }}" class="btn-edit-cat">Edit</a>
                <form method="POST" action="{{ route('kb.categories.destroy', $cat) }}"
                      onsubmit="return confirm('Delete this category and ALL its articles? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-del-cat">Delete</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            @forelse($cat->articles as $art)
            <div class="art-row">
                <span class="art-title" title="{{ $art->title }}">{{ $art->title }}</span>
                @if(! $art->is_published)
                <span class="art-draft">Draft</span>
                @endif
                <div class="art-actions">
                    <a href="{{ route('kb.articles.edit', $art) }}" class="btn-sm-edit">Edit</a>
                    <form method="POST" action="{{ route('kb.articles.toggle', $art) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="{{ $art->is_published ? 'btn-sm-untog' : 'btn-sm-toggle' }}" title="{{ $art->is_published ? 'Unpublish' : 'Publish' }}">
                            {{ $art->is_published ? '⊘' : '✓' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('kb.articles.destroy', $art) }}" style="display:inline;"
                          onsubmit="return confirm('Delete article «{{ $art->title }}»? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-sm-del">×</button>
                    </form>
                </div>
            </div>
            @empty
            <div style="padding:12px 16px;font-size:.82rem;color:#9ca3af;font-style:italic;">No articles yet</div>
            @endforelse
        </div>
        <div class="add-art-row">
            <a href="{{ route('kb.articles.create', ['category_id' => $cat->id]) }}">+ Add Article</a>
        </div>
    </div>
    @endforeach
</div>
@endif
@endsection
