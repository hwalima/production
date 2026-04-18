@extends('layouts.app')
@section('page-title', isset($article) ? 'Edit Article' : 'New Article')

@section('content')
<style>
.kb-editor-wrap { display:grid; grid-template-columns:1fr 420px; gap:20px; align-items:start; }
.kb-editor-main { min-width:0; }
.kb-editor-side { position:sticky; top:20px; }
.kb-editor-side .side-card { background:var(--card); border:1px solid var(--topbar-border); border-radius:10px; padding:18px; margin-bottom:16px; }
.kb-editor-side .side-card h4 { font-size:.82rem; font-weight:700; color:var(--text); margin:0 0 12px; }
.kb-preview-area { background:var(--card); border:1px solid var(--topbar-border); border-radius:10px; padding:20px; margin-top:16px; display:none; }
.kb-preview-area.visible { display:block; }

/* Inline editor help table */
.kb-help-table { font-size:.75rem; width:100%; border-collapse:collapse; }
.kb-help-table td { padding:3px 6px; border-bottom:1px solid var(--topbar-border); color:var(--text); vertical-align:top; }
.kb-help-table td:first-child { color:#9ca3af; font-family:monospace; white-space:nowrap; }
.kb-help-table tr:last-child td { border-bottom:none; }

/* Reuse KB body styles for preview */
.kb-body { line-height:1.75; color:var(--text); }
.kb-body h2 { font-size:1.1rem; font-weight:700; color:var(--text); margin:20px 0 8px; padding-bottom:5px; border-bottom:1px solid var(--topbar-border); }
.kb-body h3 { font-size:.97rem; font-weight:700; color:var(--text); margin:16px 0 6px; }
.kb-body p { margin:0 0 12px; }
.kb-body ul, .kb-body ol { margin:0 0 12px 20px; }
.kb-body li { margin-bottom:3px; }
.kb-body a { color:#fcb913; }
.kb-body code { background:var(--input-bg,rgba(0,0,0,.1)); border:1px solid var(--topbar-border); border-radius:3px; padding:1px 5px; font-size:.84em; font-family:monospace; color:#f59e0b; }
.kb-body pre { background:var(--input-bg,rgba(0,0,0,.15)); border:1px solid var(--topbar-border); border-radius:7px; padding:12px 14px; overflow-x:auto; margin-bottom:14px; }
.kb-body pre code { background:none; border:none; padding:0; color:var(--text); }
.kb-body .kb-table { width:100%; border-collapse:collapse; font-size:.83rem; margin:0 0 14px; }
.kb-body .kb-table th { background:rgba(252,185,19,.1); color:#fcb913; font-weight:700; padding:7px 10px; text-align:left; border-bottom:2px solid rgba(252,185,19,.25); }
.kb-body .kb-table td { padding:6px 10px; border-bottom:1px solid var(--topbar-border); }
.kb-body .kb-callout { border-radius:7px; padding:10px 14px; margin:0 0 14px; font-size:.84rem; border-left:3px solid; }
.kb-body .kb-info    { background:rgba(59,130,246,.08); border-color:#3b82f6; }
.kb-body .kb-warning { background:rgba(245,158,11,.08); border-color:#f59e0b; }
.kb-body .kb-tip     { background:rgba(52,211,153,.08); border-color:#34d399; }

@media (max-width:900px) { .kb-editor-wrap { grid-template-columns:1fr; } .kb-editor-side { position:static; } }
</style>

<div class="page-header" style="display:flex;align-items:center;gap:14px;margin-bottom:24px;">
    <a href="{{ route('kb.admin.index') }}" class="btn-cancel">← Back</a>
    <h2 class="page-title">{{ isset($article) ? 'Edit Article' : 'New Article' }}</h2>
</div>

<form method="POST"
      action="{{ isset($article) ? route('kb.articles.update', $article) : route('kb.articles.store') }}"
      id="articleForm">
    @csrf
    @if(isset($article)) @method('PUT') @endif

    <div class="kb-editor-wrap">
        {{-- Main editor column --}}
        <div class="kb-editor-main">
            <div class="form-card" style="margin-bottom:16px;">
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">Article Title <span style="color:#ef4444">*</span></label>
                    <input type="text" name="title" id="artTitle" class="form-control"
                           value="{{ old('title', $article?->title) }}"
                           placeholder="e.g. Recording Daily Production"
                           maxlength="200" required>
                    @error('title') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Content (HTML)
                        <button type="button" id="previewToggle"
                                style="margin-left:10px;font-size:.75rem;padding:2px 10px;border:1px solid rgba(252,185,19,.4);border-radius:5px;background:rgba(252,185,19,.1);color:#fcb913;cursor:pointer;">
                            Preview
                        </button>
                    </label>
                    <textarea name="content" id="artContent" class="form-control"
                              rows="24"
                              style="font-family:'Courier New',monospace;font-size:.82rem;resize:vertical;"
                              placeholder="Enter HTML content…">{{ old('content', $article?->content) }}</textarea>
                    @error('content') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Preview panel --}}
            <div class="kb-preview-area" id="previewPanel">
                <h4 style="font-size:.82rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin:0 0 14px;">Preview</h4>
                <div class="kb-body" id="previewContent"></div>
            </div>
        </div>

        {{-- Sidebar: settings + help --}}
        <div class="kb-editor-side">
            <div class="side-card">
                <h4>Settings</h4>

                <div class="form-group" style="margin-bottom:14px;">
                    <label class="form-label">Category <span style="color:#ef4444">*</span></label>
                    <select name="knowledge_base_category_id" class="form-control" required>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ old('knowledge_base_category_id', $article?->knowledge_base_category_id ?? ($selected ?? null)) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->icon }} {{ $cat->title }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div class="form-group">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control"
                               value="{{ old('sort_order', $article?->sort_order ?? 0) }}"
                               min="0" max="999">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="is_published" class="form-control">
                            <option value="1" {{ old('is_published', $article?->is_published ?? true) ? 'selected' : '' }}>Published</option>
                            <option value="0" {{ !old('is_published', $article?->is_published ?? true) ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" class="btn-add">
                        {{ isset($article) ? 'Save Changes' : 'Publish Article' }}
                    </button>
                    <a href="{{ route('kb.admin.index') }}" class="btn-cancel">Cancel</a>
                </div>

                @if(isset($article))
                <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--topbar-border);">
                    <form method="POST" action="{{ route('kb.articles.destroy', $article) }}"
                          onsubmit="return confirm('Delete this article? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                style="background:none;border:none;color:#ef4444;font-size:.8rem;cursor:pointer;padding:0;">
                            🗑 Delete this article
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <div class="side-card">
                <h4>HTML Quick Reference</h4>
                <table class="kb-help-table">
                    <tr><td>&lt;h2 id="s"&gt;Title &lt;a class="kb-anchor" href="#s"&gt;¶&lt;/a&gt;&lt;/h2&gt;</td><td>Section heading with anchor</td></tr>
                    <tr><td>&lt;h3 id="s"&gt;…&lt;/h3&gt;</td><td>Sub-heading</td></tr>
                    <tr><td>&lt;p&gt;…&lt;/p&gt;</td><td>Paragraph</td></tr>
                    <tr><td>&lt;ul&gt;&lt;li&gt;…&lt;/li&gt;&lt;/ul&gt;</td><td>Bullet list</td></tr>
                    <tr><td>&lt;ol&gt;&lt;li&gt;…&lt;/li&gt;&lt;/ol&gt;</td><td>Numbered list</td></tr>
                    <tr><td>&lt;strong&gt;…&lt;/strong&gt;</td><td>Bold text</td></tr>
                    <tr><td>&lt;code&gt;…&lt;/code&gt;</td><td>Inline code</td></tr>
                    <tr><td>&lt;pre&gt;&lt;code&gt;…&lt;/code&gt;&lt;/pre&gt;</td><td>Code block</td></tr>
                    <tr><td>&lt;table class="kb-table"&gt;…</td><td>Styled table</td></tr>
                    <tr><td>&lt;div class="kb-callout kb-info"&gt;…&lt;/div&gt;</td><td>Info callout (blue)</td></tr>
                    <tr><td>&lt;div class="kb-callout kb-warning"&gt;…&lt;/div&gt;</td><td>Warning callout (amber)</td></tr>
                    <tr><td>&lt;div class="kb-callout kb-tip"&gt;…&lt;/div&gt;</td><td>Tip callout (green)</td></tr>
                </table>
            </div>
        </div>
    </div>
</form>

<script>
const previewToggle  = document.getElementById('previewToggle');
const previewPanel   = document.getElementById('previewPanel');
const previewContent = document.getElementById('previewContent');
const artContent     = document.getElementById('artContent');

previewToggle.addEventListener('click', () => {
    const visible = previewPanel.classList.toggle('visible');
    previewToggle.textContent = visible ? 'Hide Preview' : 'Preview';
    if (visible) {
        previewContent.innerHTML = artContent.value;
    }
});

artContent.addEventListener('input', () => {
    if (previewPanel.classList.contains('visible')) {
        previewContent.innerHTML = artContent.value;
    }
});
</script>
@endsection
