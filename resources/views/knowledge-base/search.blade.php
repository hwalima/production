@extends('layouts.app')
@section('page-title', 'Search — ' . ($q ? "\"$q\"" : 'Knowledge Base'))

@section('content')
<style>
.kb-wrap { display:flex; gap:0; min-height:calc(100vh - 60px); background:var(--bg); }
.kb-sidebar {
    width:272px; flex-shrink:0; background:var(--card);
    border-right:1px solid var(--topbar-border);
    display:flex; flex-direction:column;
    position:sticky; top:0; height:calc(100vh - 60px); overflow:hidden;
}
.kb-sidebar-head { padding:14px 14px 10px; border-bottom:1px solid var(--topbar-border); flex-shrink:0; }
.kb-sidebar-head h1 { font-size:.95rem; font-weight:700; color:var(--text); margin:0 0 10px 0; display:flex; align-items:center; gap:7px; }
.kb-search-box { position:relative; }
.kb-search-box input { width:100%; padding:7px 10px 7px 30px; border-radius:6px; border:1px solid var(--topbar-border); background:var(--input-bg,var(--bg)); color:var(--text); font-size:.82rem; outline:none; box-sizing:border-box; }
.kb-search-box input:focus { border-color:#fcb913; }
.kb-search-box .kb-search-icon { position:absolute; left:8px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:.85rem; pointer-events:none; }
.kb-nav { flex:1; overflow-y:auto; padding:8px 0 20px; }
.kb-nav::-webkit-scrollbar { width:4px; }
.kb-nav::-webkit-scrollbar-thumb { background:rgba(156,163,175,.3); border-radius:2px; }
.kb-cat-header { display:flex; align-items:center; gap:6px; padding:8px 14px 5px; font-size:.7rem; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:rgba(252,185,19,.75); cursor:pointer; user-select:none; }
.kb-cat-header .kb-cat-toggle { margin-left:auto; font-size:.65rem; color:rgba(252,185,19,.5); transition:transform .2s; }
.kb-cat-section.collapsed .kb-cat-toggle { transform:rotate(-90deg); }
.kb-cat-section.collapsed .kb-cat-articles { display:none; }
.kb-cat-articles { list-style:none; margin:0; padding:0 0 6px; }
.kb-cat-articles li a { display:block; padding:5px 14px 5px 28px; font-size:.82rem; color:var(--text); opacity:.75; text-decoration:none; border-left:2px solid transparent; transition:opacity .15s, border-color .15s, background .15s; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.kb-cat-articles li a:hover { opacity:1; background:rgba(252,185,19,.06); }
.kb-content { flex:1; min-width:0; padding:32px 40px 60px; max-width:860px; }
.kb-search-result { padding:16px; border:1px solid var(--topbar-border); border-radius:8px; margin-bottom:12px; background:var(--card); text-decoration:none; display:block; transition:border-color .15s, background .15s; }
.kb-search-result:hover { border-color:#fcb913; background:rgba(252,185,19,.04); }
.kb-search-result .res-cat { font-size:.72rem; font-weight:700; color:#fcb913; text-transform:uppercase; letter-spacing:.08em; margin-bottom:4px; }
.kb-search-result .res-title { font-size:1rem; font-weight:600; color:var(--text); margin-bottom:6px; }
.kb-search-result .res-excerpt { font-size:.83rem; color:#9ca3af; line-height:1.55; }
.kb-search-result .res-excerpt mark { background:rgba(252,185,19,.25); color:var(--text); border-radius:2px; padding:0 2px; }
@media (max-width:768px) { .kb-wrap{flex-direction:column} .kb-sidebar{width:100%;height:auto;position:relative;border-right:none;border-bottom:1px solid var(--topbar-border)} .kb-content{padding:20px 18px 40px} }
</style>

<div class="kb-wrap">
    <aside class="kb-sidebar">
        <div class="kb-sidebar-head">
            <h1><span>📚</span> Knowledge Base</h1>
            <form action="{{ route('kb.search') }}" method="GET" class="kb-search-box">
                <span class="kb-search-icon">🔍</span>
                <input type="search" name="q"
                       placeholder="Search articles…"
                       value="{{ $q }}"
                       autocomplete="off"
                       autofocus>
            </form>
        </div>
        <nav class="kb-nav">
            @foreach($categories as $cat)
            <div class="kb-cat-section collapsed" data-cat="{{ $cat->id }}">
                <div class="kb-cat-header" onclick="this.parentElement.classList.toggle('collapsed')">
                    <span>{{ $cat->icon }}</span>
                    <span>{{ $cat->title }}</span>
                    <span class="kb-cat-toggle">▼</span>
                </div>
                <ul class="kb-cat-articles">
                    @foreach($cat->articles as $art)
                    <li>
                        <a href="{{ route('kb.show', [$cat->slug, $art->slug]) }}">{{ $art->title }}</a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </nav>
    </aside>

    <main class="kb-content">
        <div style="margin-bottom:20px;">
            <a href="{{ route('kb.index') }}" style="color:#fcb913;text-decoration:none;font-size:.82rem;">← Back to Help</a>
        </div>

        @if($q)
            <h1 style="font-size:1.4rem;font-weight:700;color:var(--text);margin:0 0 4px;">
                Search results for "{{ $q }}"
            </h1>
            <p style="color:#9ca3af;font-size:.85rem;margin:0 0 24px;">
                {{ $results->count() }} {{ Str::plural('article', $results->count()) }} found
            </p>

            @forelse($results as $result)
            @php
                // Build a plain-text excerpt from content
                $plain   = strip_tags($result->content);
                $pos     = stripos($plain, $q);
                $start   = max(0, $pos - 80);
                $excerpt = ($start > 0 ? '…' : '') . substr($plain, $start, 220) . '…';
                // Highlight query in excerpt
                $highlighted = preg_replace('/(' . preg_quote($q, '/') . ')/i', '<mark>$1</mark>', e($excerpt));
            @endphp
            <a href="{{ route('kb.show', [$result->category->slug, $result->slug]) }}"
               class="kb-search-result">
                <div class="res-cat">{{ $result->category->icon }} {{ $result->category->title }}</div>
                <div class="res-title">{{ $result->title }}</div>
                <div class="res-excerpt">{!! $highlighted !!}</div>
            </a>
            @empty
            <div style="padding:40px 0;text-align:center;color:#9ca3af;">
                <div style="font-size:2rem;margin-bottom:12px;">🔍</div>
                <p>No articles found for <strong style="color:var(--text);">"{{ $q }}"</strong>.</p>
                <p style="font-size:.85rem;">Try a different keyword or browse the categories in the sidebar.</p>
            </div>
            @endforelse
        @else
            <div style="padding:40px 0;text-align:center;color:#9ca3af;">
                <div style="font-size:2rem;margin-bottom:12px;">🔍</div>
                <p>Enter a keyword above to search the knowledge base.</p>
            </div>
        @endif
    </main>
</div>
@endsection
