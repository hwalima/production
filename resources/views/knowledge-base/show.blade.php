@extends('layouts.app')
@section('page-title', isset($article) ? $article->title . ' — Help' : 'Knowledge Base')

@section('content')
<style>
/* ── KB layout ─────────────────────────────────────────────── */
.kb-wrap {
    display: flex;
    gap: 0;
    min-height: calc(100vh - 60px);
    background: var(--bg);
}

/* ── Sidebar ─────────────────────────────────────────────────  */
.kb-sidebar {
    width: 272px;
    flex-shrink: 0;
    background: var(--card);
    border-right: 1px solid var(--topbar-border);
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 0;
    height: calc(100vh - 60px);
    overflow: hidden;
}
.kb-sidebar-head {
    padding: 14px 14px 10px;
    border-bottom: 1px solid var(--topbar-border);
    flex-shrink: 0;
}
.kb-sidebar-head h1 {
    font-size: .95rem;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 7px;
}
.kb-search-box {
    position: relative;
}
.kb-search-box input {
    width: 100%;
    padding: 7px 10px 7px 30px;
    border-radius: 6px;
    border: 1px solid var(--topbar-border);
    background: var(--input-bg, var(--bg));
    color: var(--text);
    font-size: .82rem;
    outline: none;
    box-sizing: border-box;
    transition: border-color .15s;
}
.kb-search-box input:focus { border-color: #fcb913; }
.kb-search-box .kb-search-icon {
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: .85rem;
    pointer-events: none;
}
.kb-nav {
    flex: 1;
    overflow-y: auto;
    padding: 8px 0 20px;
}
.kb-nav::-webkit-scrollbar { width: 4px; }
.kb-nav::-webkit-scrollbar-thumb { background: rgba(156,163,175,.3); border-radius: 2px; }

/* Category section */
.kb-cat-header {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px 5px;
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: rgba(252,185,19,.75);
    cursor: pointer;
    user-select: none;
}
.kb-cat-header .kb-cat-toggle {
    margin-left: auto;
    font-size: .65rem;
    color: rgba(252,185,19,.5);
    transition: transform .2s;
}
.kb-cat-section.collapsed .kb-cat-toggle { transform: rotate(-90deg); }
.kb-cat-section.collapsed .kb-cat-articles { display: none; }

.kb-cat-articles {
    list-style: none;
    margin: 0;
    padding: 0 0 6px;
}
.kb-cat-articles li a {
    display: block;
    padding: 5px 14px 5px 28px;
    font-size: .82rem;
    color: var(--text);
    opacity: .75;
    text-decoration: none;
    border-left: 2px solid transparent;
    transition: opacity .15s, border-color .15s, background .15s;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.kb-cat-articles li a:hover {
    opacity: 1;
    background: rgba(252,185,19,.06);
}
.kb-cat-articles li.active a {
    opacity: 1;
    color: #fcb913;
    border-left-color: #fcb913;
    background: rgba(252,185,19,.08);
    font-weight: 600;
}

/* ── Content area ──────────────────────────────────────────── */
.kb-content {
    flex: 1;
    min-width: 0;
    padding: 32px 40px 60px;
    max-width: 860px;
}
.kb-breadcrumb {
    font-size: .78rem;
    color: #9ca3af;
    margin-bottom: 10px;
}
.kb-breadcrumb a { color: #fcb913; text-decoration: none; }
.kb-breadcrumb a:hover { text-decoration: underline; }
.kb-breadcrumb span { margin: 0 5px; }

.kb-article-title {
    font-size: 1.65rem;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 6px;
    line-height: 1.25;
}
.kb-article-meta {
    font-size: .78rem;
    color: #9ca3af;
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--topbar-border);
}
.kb-article-meta .kb-cat-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: rgba(252,185,19,.12);
    color: #fcb913;
    border: 1px solid rgba(252,185,19,.25);
    border-radius: 99px;
    padding: 2px 9px;
    font-size: .72rem;
    font-weight: 700;
    margin-right: 8px;
}

/* ── Article body typography ─────────────────────────────── */
.kb-body { line-height: 1.75; color: var(--text); }
.kb-body h2 {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--text);
    margin: 28px 0 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--topbar-border);
    display: flex;
    align-items: center;
    gap: 6px;
}
.kb-body h3 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text);
    margin: 20px 0 8px;
}
.kb-body p { margin: 0 0 14px; }
.kb-body ul, .kb-body ol {
    margin: 0 0 14px 22px;
    padding: 0;
}
.kb-body li { margin-bottom: 4px; }
.kb-body a { color: #fcb913; text-decoration: none; }
.kb-body a:hover { text-decoration: underline; }
.kb-body code {
    background: var(--input-bg, rgba(0,0,0,.1));
    border: 1px solid var(--topbar-border);
    border-radius: 4px;
    padding: 1px 5px;
    font-size: .84em;
    font-family: 'Courier New', monospace;
    color: #f59e0b;
}
.kb-body pre {
    background: var(--input-bg, rgba(0,0,0,.15));
    border: 1px solid var(--topbar-border);
    border-radius: 8px;
    padding: 14px 16px;
    overflow-x: auto;
    margin: 0 0 16px;
}
.kb-body pre code {
    background: none;
    border: none;
    padding: 0;
    font-size: .83rem;
    color: var(--text);
}
.kb-anchor {
    font-size: .75rem;
    color: #9ca3af;
    text-decoration: none;
    opacity: 0;
    transition: opacity .15s;
    margin-left: 4px;
}
h2:hover .kb-anchor, h3:hover .kb-anchor { opacity: 1; }

/* Tables */
.kb-body .kb-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .84rem;
    margin: 0 0 18px;
}
.kb-body .kb-table th {
    background: rgba(252,185,19,.1);
    color: #fcb913;
    font-weight: 700;
    padding: 8px 12px;
    text-align: left;
    border-bottom: 2px solid rgba(252,185,19,.25);
}
.kb-body .kb-table td {
    padding: 7px 12px;
    border-bottom: 1px solid var(--topbar-border);
    vertical-align: top;
}
.kb-body .kb-table tr:last-child td { border-bottom: none; }
.kb-body .kb-table tr:hover td { background: rgba(252,185,19,.04); }

/* Callouts */
.kb-callout {
    border-radius: 8px;
    padding: 12px 16px;
    margin: 0 0 18px;
    font-size: .85rem;
    border-left: 3px solid;
}
.kb-info    { background: rgba(59,130,246,.08);  border-color: #3b82f6; color: var(--text); }
.kb-warning { background: rgba(245,158,11,.08);  border-color: #f59e0b; color: var(--text); }
.kb-tip     { background: rgba(52,211,153,.08);  border-color: #34d399; color: var(--text); }
.kb-danger  { background: rgba(239,68,68,.08);   border-color: #ef4444; color: var(--text); }

/* ── Prev / Next ─────────────────────────────────────────── */
.kb-prev-next {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    margin-top: 48px;
    padding-top: 24px;
    border-top: 1px solid var(--topbar-border);
}
.kb-prev-next a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 18px;
    border-radius: 8px;
    border: 1px solid var(--topbar-border);
    background: var(--card);
    color: var(--text);
    text-decoration: none;
    font-size: .84rem;
    transition: border-color .15s, background .15s;
    max-width: 48%;
}
.kb-prev-next a:hover {
    border-color: #fcb913;
    background: rgba(252,185,19,.06);
}
.kb-prev-next a .pn-label {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #fcb913;
    display: block;
}
.kb-prev-next a .pn-title { color: var(--text); }
.kb-prev-next .pn-next { margin-left: auto; text-align: right; }

/* ── Empty state ─────────────────────────────────────────── */
.kb-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 20px;
    text-align: center;
    color: #9ca3af;
}
.kb-empty .kb-empty-icon { font-size: 3rem; margin-bottom: 14px; }
.kb-empty h2 { color: var(--text); margin-bottom: 8px; font-size: 1.2rem; }

/* ── Admin bar ───────────────────────────────────────────── */
.kb-admin-bar {
    display: flex;
    gap: 10px;
    align-items: center;
    padding: 10px 16px;
    background: rgba(252,185,19,.07);
    border: 1px solid rgba(252,185,19,.2);
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: .82rem;
    color: var(--text);
}
.kb-admin-bar a, .kb-admin-bar button {
    font-size: .8rem;
    padding: 4px 12px;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    border: 1px solid;
}
.kb-admin-bar .btn-edit {
    background: rgba(252,185,19,.15);
    border-color: rgba(252,185,19,.4);
    color: #fcb913;
}
.kb-admin-bar .btn-edit:hover { background: rgba(252,185,19,.3); }

/* ── Responsive ──────────────────────────────────────────── */
.kb-sidebar-toggle-btn {
    display: none;
    background: var(--card);
    border: 1px solid var(--topbar-border);
    border-radius: 6px;
    padding: 6px 12px;
    color: var(--text);
    font-size: .82rem;
    cursor: pointer;
    margin-bottom: 12px;
    gap: 6px;
    align-items: center;
}

@media (max-width: 768px) {
    .kb-wrap { flex-direction: column; }
    .kb-sidebar {
        width: 100%;
        height: auto;
        position: relative;
        border-right: none;
        border-bottom: 1px solid var(--topbar-border);
    }
    .kb-sidebar-toggle-btn { display: flex; margin: 10px 14px; }
    .kb-nav { display: none; }
    .kb-nav.mobile-open { display: block; }
    .kb-content { padding: 20px 18px 40px; }
    .kb-prev-next a { max-width: 100%; }
}
</style>

<div class="kb-wrap">

    {{-- ═══════════ SIDEBAR ═══════════ --}}
    <aside class="kb-sidebar">
        <div class="kb-sidebar-head">
            <h1><span>📚</span> Knowledge Base</h1>
            <form action="{{ route('kb.search') }}" method="GET" class="kb-search-box">
                <span class="kb-search-icon">🔍</span>
                <input type="search" name="q"
                       placeholder="Search articles…"
                       value="{{ request('q') }}"
                       autocomplete="off">
            </form>
        </div>

        <button class="kb-sidebar-toggle-btn" id="kbNavToggle">
            ☰ Browse Topics
        </button>

        <nav class="kb-nav" id="kbNav">
            @foreach($categories as $cat)
            @php
                $isActiveCategory = isset($category) && $category->id === $cat->id;
            @endphp
            <div class="kb-cat-section {{ $isActiveCategory ? '' : 'collapsed' }}" data-cat="{{ $cat->id }}">
                <div class="kb-cat-header" onclick="kbToggleCat(this.parentElement)">
                    <span>{{ $cat->icon }}</span>
                    <span>{{ $cat->title }}</span>
                    <span class="kb-cat-toggle">▼</span>
                </div>
                <ul class="kb-cat-articles">
                    @foreach($cat->articles as $art)
                    <li class="{{ isset($article) && $article->id === $art->id ? 'active' : '' }}">
                        <a href="{{ route('kb.show', [$cat->slug, $art->slug]) }}"
                           title="{{ $art->title }}">{{ $art->title }}</a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach

            @if(auth()->user()?->isAdminOrAbove())
            <div style="padding: 16px 14px 4px; border-top: 1px solid var(--topbar-border); margin-top: 8px;">
                <a href="{{ route('kb.admin.index') }}"
                   style="font-size:.78rem;color:#fcb913;text-decoration:none;display:flex;align-items:center;gap:5px;opacity:.8;"
                   title="Manage KB content">⚙ Manage Content</a>
            </div>
            @endif
        </nav>
    </aside>

    {{-- ═══════════ MAIN CONTENT ═══════════ --}}
    <main class="kb-content">
        @if(isset($article))

            {{-- Admin edit bar --}}
            @if(auth()->user()?->isAdminOrAbove())
            <div class="kb-admin-bar">
                <span>✏️ You can edit this article.</span>
                <a href="{{ route('kb.articles.edit', $article) }}" class="btn-edit">Edit Article</a>
                <a href="{{ route('kb.articles.create', ['category_id' => $category->id]) }}" class="btn-edit">+ New Article</a>
            </div>
            @endif

            {{-- Breadcrumb --}}
            <div class="kb-breadcrumb">
                <a href="{{ route('kb.index') }}">Help</a>
                <span>/</span>
                {{ $category->title }}
                <span>/</span>
                {{ $article->title }}
            </div>

            {{-- Title + meta --}}
            <h1 class="kb-article-title">{{ $article->title }}</h1>
            <div class="kb-article-meta">
                <span class="kb-cat-badge">{{ $category->icon }} {{ $category->title }}</span>
                Updated {{ $article->updated_at->diffForHumans() }}
            </div>

            {{-- Body --}}
            <div class="kb-body">
                {!! $article->content !!}
            </div>

            {{-- Prev / Next --}}
            @if($prev || $next)
            <div class="kb-prev-next">
                @if($prev)
                <a href="{{ route('kb.show', [$prev->category->slug, $prev->slug]) }}">
                    <span>←</span>
                    <div>
                        <span class="pn-label">Previous</span>
                        <span class="pn-title">{{ $prev->title }}</span>
                    </div>
                </a>
                @else
                <div></div>
                @endif

                @if($next)
                <a href="{{ route('kb.show', [$next->category->slug, $next->slug]) }}" class="pn-next">
                    <div>
                        <span class="pn-label">Next</span>
                        <span class="pn-title">{{ $next->title }}</span>
                    </div>
                    <span>→</span>
                </a>
                @endif
            </div>
            @endif

        @else
            {{-- Empty state --}}
            <div class="kb-empty">
                <div class="kb-empty-icon">📚</div>
                <h2>Knowledge Base</h2>
                <p>No articles have been published yet.</p>
                @if(auth()->user()?->isAdminOrAbove())
                <a href="{{ route('kb.admin.index') }}" class="btn-add" style="margin-top:16px;">Get Started</a>
                @endif
            </div>
        @endif
    </main>
</div>

<script>
function kbToggleCat(section) {
    section.classList.toggle('collapsed');
}

// Mobile nav toggle
const kbNavToggle = document.getElementById('kbNavToggle');
const kbNav       = document.getElementById('kbNav');
if (kbNavToggle) {
    kbNavToggle.addEventListener('click', () => kbNav.classList.toggle('mobile-open'));
}

// Scroll active article into view in sidebar
document.addEventListener('DOMContentLoaded', () => {
    const activeItem = document.querySelector('.kb-cat-articles li.active a');
    if (activeItem) {
        activeItem.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
});
</script>
@endsection
