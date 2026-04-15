@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Application Settings')
@section('content')

<div class="max-w-3xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold">Application Settings</h1>

    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- ══════════════ COMPANY INFO ══════════════ --}}
        <div class="rounded-xl shadow p-6 space-y-4" style="background:var(--card);">
            <h2 class="text-base font-semibold pb-2" style="border-bottom:2px solid #fcb913;">Company Information</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium mb-1">Company Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="company_name"
                           value="{{ old('company_name', $settings['company_name'] ?? 'Epoch Mines and Resources') }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);" required>
                    @error('company_name')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Location</label>
                    <input type="text" name="company_location"
                           value="{{ old('company_location', $settings['company_location'] ?? 'Filabusi, Zimbabwe') }}"
                           placeholder="e.g. Filabusi, Zimbabwe"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Address</label>
                    <input type="text" name="company_address"
                           value="{{ old('company_address', $settings['company_address'] ?? '') }}"
                           placeholder="P.O. Box / Street"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input type="text" name="company_phone"
                           value="{{ old('company_phone', $settings['company_phone'] ?? '') }}"
                           placeholder="+263 ..."
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="company_email"
                           value="{{ old('company_email', $settings['company_email'] ?? '') }}"
                           placeholder="info@epochmines.co.zw"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                    @error('company_email')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Website</label>
                    <input type="url" name="company_website"
                           value="{{ old('company_website', $settings['company_website'] ?? '') }}"
                           placeholder="https://..."
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                    @error('company_website')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Logo upload --}}
            <div>
                <label class="block text-sm font-medium mb-2">Company Logo</label>
                <div class="flex items-start gap-5">
                    {{-- Current logo preview --}}
                    <div class="flex-shrink-0">
                        @if(!empty($settings['logo_path']))
                            <img src="{{ asset('storage/' . $settings['logo_path']) }}"
                                 alt="Current Logo" id="logoPreview"
                                 class="h-20 w-auto rounded-lg object-contain"
                                 style="background:#f3f4f6;padding:6px;border:1px solid var(--topbar-border);">
                        @else
                            <div id="logoPreview" class="h-20 w-32 rounded-lg flex items-center justify-center text-3xl"
                                 style="background:#f3f4f6;border:1px dashed #d1d5db;">⛏</div>
                        @endif
                    </div>

                    <div class="flex-1 space-y-2">
                        <label for="logoInput"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-semibold text-sm cursor-pointer"
                               style="background:#fcb913;color:#001a4d;">
                            &#128247; Choose Logo
                        </label>
                        <input type="file" id="logoInput" name="logo" accept="image/*" class="hidden"
                               onchange="previewLogo(this)">
                        <p class="text-xs" style="color:#9ca3af;">
                            PNG, JPG, SVG or WebP · Max 2MB<br>
                            The uploaded logo will replace the sidebar logo and browser favicon.
                        </p>
                        @error('logo')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                        <p id="logoFileName" class="text-xs font-medium" style="color:#fcb913;"></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════ OPERATIONAL DEFAULTS ══════════════ --}}
        <div class="rounded-xl shadow p-6 space-y-4" style="background:var(--card);">
            <h2 class="text-base font-semibold pb-2" style="border-bottom:2px solid #fcb913;">Default Daily Costs</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">ZESA Cost / Day ($)</label>
                    <input type="number" name="zesa_daily" step="0.01" min="0"
                           value="{{ old('zesa_daily', $settings['zesa_daily'] ?? 633) }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);" required>
                    @error('zesa_daily')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Diesel Cost / Day ($)</label>
                    <input type="number" name="diesel_daily" step="0.01" min="0"
                           value="{{ old('diesel_daily', $settings['diesel_daily'] ?? 428) }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);" required>
                    @error('diesel_daily')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Labour Cost / Day ($)</label>
                    <input type="number" name="labour_daily" step="0.01" min="0"
                           value="{{ old('labour_daily', $settings['labour_daily'] ?? 0) }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);" required>
                    @error('labour_daily')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ══════════════ WORK SHIFTS ══════════════ --}}
        <div class="rounded-xl shadow p-6 space-y-3" style="background:var(--card);">
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #fcb913;padding-bottom:8px;">
                <h2 class="text-base font-semibold">Work Shifts</h2>
                <a href="{{ route('shifts.index') }}" class="btn-add" style="padding:6px 14px;font-size:.78rem;">Manage &rarr;</a>
            </div>
            @php $shifts = \App\Models\Shift::orderBy('name')->get(); @endphp
            @if($shifts->isEmpty())
            <p style="font-size:.8rem;color:#9ca3af;">No shifts configured yet.</p>
            @else
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;">
                @foreach($shifts as $s)
                <span style="background:{{ $s->is_active ? 'rgba(252,185,19,.15)' : 'rgba(156,163,175,.1)' }};color:{{ $s->is_active ? '#fcb913' : '#9ca3af' }};border:1px solid {{ $s->is_active ? '#fcb913' : '#6b7280' }};border-radius:20px;padding:3px 12px;font-size:.75rem;font-weight:600;">
                    {{ $s->name }}
                </span>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ══════════════ MINING SITES ══════════════ --}}
        <div class="rounded-xl shadow p-6 space-y-3" style="background:var(--card);">
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #fcb913;padding-bottom:8px;">
                <h2 class="text-base font-semibold">Mining Levels / Sites</h2>
                <a href="{{ route('mining-sites.index') }}" class="btn-add" style="padding:6px 14px;font-size:.78rem;">Manage &rarr;</a>
            </div>
            @php $sites = \App\Models\MiningSite::orderBy('name')->get(); @endphp
            @if($sites->isEmpty())
            <p style="font-size:.8rem;color:#9ca3af;">No mining sites configured yet.</p>
            @else
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;">
                @foreach($sites as $site)
                <span style="background:{{ $site->is_active ? 'rgba(252,185,19,.15)' : 'rgba(156,163,175,.1)' }};color:{{ $site->is_active ? '#fcb913' : '#9ca3af' }};border:1px solid {{ $site->is_active ? '#fcb913' : '#6b7280' }};border-radius:20px;padding:3px 12px;font-size:.75rem;font-weight:600;">
                    {{ $site->name }}
                </span>
                @endforeach
            </div>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-6 py-2 rounded-lg font-semibold text-sm" style="background:#fcb913;color:#001a4d;">
                Save All Settings
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('logoPreview');
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                // Replace the placeholder div with an img
                const img = document.createElement('img');
                img.id = 'logoPreview';
                img.src = e.target.result;
                img.alt = 'Logo Preview';
                img.className = 'h-20 w-auto rounded-lg object-contain';
                img.style.cssText = 'background:#f3f4f6;padding:6px;border:1px solid var(--topbar-border);';
                preview.replaceWith(img);
            }
        };
        reader.readAsDataURL(input.files[0]);
        document.getElementById('logoFileName').textContent = '✓ ' + input.files[0].name;
    }
}
</script>
@endpush
@endsection

