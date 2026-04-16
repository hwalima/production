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

                {{-- Mine GPS --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Mine Latitude</label>
                    <input type="number" name="mine_latitude" step="any"
                           value="{{ old('mine_latitude', $settings['mine_latitude'] ?? '-20.52') }}"
                           placeholder="-20.52"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                    @error('mine_latitude')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Mine Longitude</label>
                    <input type="number" name="mine_longitude" step="any"
                           value="{{ old('mine_longitude', $settings['mine_longitude'] ?? '29.33') }}"
                           placeholder="29.33"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                    @error('mine_longitude')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                    <p class="text-xs mt-1" style="color:#9ca3af;">Used to show accurate weather for the mine site. <a href="https://www.latlong.net/" target="_blank" style="color:#fcb913;">Find coordinates ↗</a></p>
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

        {{-- ══════════════ CURRENCY & REGION ══════════════ --}}
        <div class="rounded-xl shadow p-6 space-y-4" style="background:var(--card);">
            <h2 class="text-base font-semibold pb-2" style="border-bottom:2px solid #fcb913;">Currency &amp; Region</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Currency</label>
                    <select name="currency_code" id="currencyCodeSelect"
                            class="w-full border rounded-lg px-3 py-2 text-sm"
                            style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);"
                            onchange="syncCurrencySymbol(this.value)">
                        @php
                            $currencies = [
                                'USD' => ['name' => 'US Dollar',              'symbol' => '$'],
                                'ZWL' => ['name' => 'Zimbabwe Dollar',        'symbol' => 'ZWL'],
                                'ZIG' => ['name' => 'Zimbabwe Gold (ZiG)',    'symbol' => 'ZiG'],
                                'ZAR' => ['name' => 'South African Rand',     'symbol' => 'R'],
                                'BWP' => ['name' => 'Botswana Pula',          'symbol' => 'P'],
                                'EUR' => ['name' => 'Euro',                   'symbol' => '€'],
                                'GBP' => ['name' => 'British Pound',          'symbol' => '£'],
                                'XAU' => ['name' => 'Gold (troy oz)',         'symbol' => 'Au'],
                            ];
                            $savedCode = old('currency_code', $settings['currency_code'] ?? 'USD');
                        @endphp
                        @foreach($currencies as $code => $info)
                            <option value="{{ $code }}" {{ $savedCode === $code ? 'selected' : '' }}>
                                {{ $info['symbol'] }} — {{ $info['name'] }} ({{ $code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Currency Symbol</label>
                    <input type="text" name="currency_symbol" id="currencySymbolInput"
                           value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '$') }}"
                           maxlength="10"
                           placeholder="e.g. $ or ZWL"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                    <p class="text-xs mt-1" style="color:#9ca3af;">Auto-filled from the currency selection, or enter a custom symbol.</p>
                    @error('currency_symbol')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Preview --}}
            <p class="text-xs" style="color:#9ca3af;">
                Preview: monetary values will display as
                <strong id="currencyPreview" style="color:var(--text);">{{ $settings['currency_symbol'] ?? '$' }}1,234.56</strong>
            </p>
        </div>

        {{-- ══════════════ OPERATIONAL DEFAULTS ══════════════ --}}
        <div class="rounded-xl shadow p-6 space-y-4" style="background:var(--card);">
            <h2 class="text-base font-semibold pb-2" style="border-bottom:2px solid #fcb913;">Default Daily Costs</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">ZESA Cost / Day ({{ $currencySymbol }})</label>
                    <input type="number" name="zesa_daily" step="0.01" min="0"
                           value="{{ old('zesa_daily', $settings['zesa_daily'] ?? 633) }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);" required>
                    @error('zesa_daily')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Diesel Cost / Day ({{ $currencySymbol }})</label>
                    <input type="number" name="diesel_daily" step="0.01" min="0"
                           value="{{ old('diesel_daily', $settings['diesel_daily'] ?? 428) }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);" required>
                    @error('diesel_daily')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Labour Cost / Day ({{ $currencySymbol }})</label>
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

        {{-- ══════════════ MINING DEPARTMENTS ══════════════ --}}
        <div class="rounded-xl shadow p-6 space-y-3" style="background:var(--card);">
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #fcb913;padding-bottom:8px;">
                <h2 class="text-base font-semibold">Mining Departments</h2>
                <a href="{{ route('mining-departments.index') }}" class="btn-add" style="padding:6px 14px;font-size:.78rem;">Manage &rarr;</a>
            </div>
            @php $depts = \App\Models\MiningDepartment::orderBy('name')->get(); @endphp
            @if($depts->isEmpty())
            <p style="font-size:.8rem;color:#9ca3af;">No departments configured yet.</p>
            @else
            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;">
                @foreach($depts as $dept)
                <span style="background:{{ $dept->is_active ? 'rgba(252,185,19,.15)' : 'rgba(156,163,175,.1)' }};color:{{ $dept->is_active ? '#fcb913' : '#9ca3af' }};border:1px solid {{ $dept->is_active ? '#fcb913' : '#6b7280' }};border-radius:20px;padding:3px 12px;font-size:.75rem;font-weight:600;">
                    {{ $dept->name }}
                </span>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ══════════════ EMAIL / SMTP ══════════════ --}}
        <div class="rounded-xl shadow p-6 space-y-4" style="background:var(--card);">
            <h2 class="text-base font-semibold pb-2" style="border-bottom:2px solid #fcb913;">
                📧 Email / SMTP Settings
            </h2>
            <p style="font-size:.78rem;color:#9ca3af;margin-top:6px;">
                Used for password resets, notifications and reports. Works with Gmail, Outlook, SendGrid, Mailgun, etc.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">SMTP Host</label>
                    <input type="text" name="mail_host"
                           value="{{ old('mail_host', $settings['mail_host'] ?? '') }}"
                           placeholder="smtp.gmail.com"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                    @error('mail_host')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">SMTP Port</label>
                    <input type="number" name="mail_port"
                           value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}"
                           placeholder="587"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                    @error('mail_port')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">SMTP Username</label>
                    <input type="text" name="mail_username" autocomplete="off"
                           value="{{ old('mail_username', $settings['mail_username'] ?? '') }}"
                           placeholder="you@gmail.com"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                    @error('mail_username')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">SMTP Password</label>
                    <div style="position:relative;display:flex;align-items:center;">
                        <input type="password" name="mail_password" id="mailPassword" autocomplete="new-password"
                               placeholder="{{ !empty($settings['mail_password'] ?? '') ? '••••••••' : 'App password / API key' }}"
                               class="w-full border rounded-lg px-3 py-2 text-sm"
                               style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);padding-right:44px;">
                        <button type="button" onclick="toggleMailPw()" style="position:absolute;right:10px;background:none;border:none;cursor:pointer;color:#9ca3af;display:flex;align-items:center;">
                            <svg id="mailEye" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="mailEyeOff" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    <p style="font-size:.7rem;color:#9ca3af;margin-top:3px;">Leave blank to keep the existing password.</p>
                    @error('mail_password')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Encryption</label>
                    <select name="mail_encryption"
                            class="w-full border rounded-lg px-3 py-2 text-sm"
                            style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                        @foreach(['tls' => 'TLS (recommended)', 'ssl' => 'SSL', 'starttls' => 'STARTTLS', '' => 'None'] as $val => $label)
                            <option value="{{ $val }}" {{ old('mail_encryption', $settings['mail_encryption'] ?? 'tls') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">From Address</label>
                    <input type="email" name="mail_from_address"
                           value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}"
                           placeholder="noreply@epochmines.co.zw"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                    @error('mail_from_address')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium mb-1">From Name</label>
                    <input type="text" name="mail_from_name"
                           value="{{ old('mail_from_name', $settings['mail_from_name'] ?? '') }}"
                           placeholder="{{ $settings['company_name'] ?? 'My Mine' }}"
                           class="w-full border rounded-lg px-3 py-2 text-sm"
                           style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                </div>
            </div>

            {{-- Quick reference table --}}
            <details style="margin-top:4px;">
                <summary style="font-size:.78rem;color:#fcb913;cursor:pointer;font-weight:600;">Common SMTP settings ▾</summary>
                <div style="margin-top:10px;overflow-x:auto;">
                    <table style="width:100%;font-size:.75rem;border-collapse:collapse;">
                        <thead>
                            <tr style="background:rgba(252,185,19,.08);">
                                <th style="padding:5px 10px;text-align:left;color:#9ca3af;">Provider</th>
                                <th style="padding:5px 10px;text-align:left;color:#9ca3af;">Host</th>
                                <th style="padding:5px 10px;text-align:left;color:#9ca3af;">Port</th>
                                <th style="padding:5px 10px;text-align:left;color:#9ca3af;">Encryption</th>
                                <th style="padding:5px 10px;text-align:left;color:#9ca3af;">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach([
                                ['Gmail',    'smtp.gmail.com',           '587', 'TLS',  'Use an App Password (2FA required)'],
                                ['Outlook',  'smtp.office365.com',       '587', 'STARTTLS', 'Microsoft 365 / Outlook.com'],
                                ['SendGrid', 'smtp.sendgrid.net',        '587', 'TLS',  'Username = apikey, Password = API key'],
                                ['Mailgun',  'smtp.mailgun.org',         '587', 'TLS',  'SMTP credentials from Mailgun dashboard'],
                                ['Yahoo',    'smtp.mail.yahoo.com',      '587', 'TLS',  'Generate an app password'],
                            ] as [$p,$h,$pt,$e,$n])
                            <tr style="border-top:1px solid var(--topbar-border);">
                                <td style="padding:5px 10px;font-weight:600;color:var(--text);">{{ $p }}</td>
                                <td style="padding:5px 10px;color:#fcb913;font-family:monospace;">{{ $h }}</td>
                                <td style="padding:5px 10px;">{{ $pt }}</td>
                                <td style="padding:5px 10px;">{{ $e }}</td>
                                <td style="padding:5px 10px;color:#9ca3af;">{{ $n }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </div>

        {{-- ══════════════ TEST EMAIL ══════════════ --}}

        <div class="flex items-center gap-3">
            <button type="submit" class="px-6 py-2 rounded-lg font-semibold text-sm" style="background:#fcb913;color:#001a4d;">
                Save All Settings
            </button>
        </div>
    </form>

    {{-- ══════════════ TEST EMAIL (outside main form) ══════════════ --}}
    <div class="rounded-xl shadow p-6" style="background:var(--card);border:1px solid rgba(252,185,19,.25);">
        <h2 class="text-base font-semibold pb-2" style="border-bottom:2px solid #fcb913;">✉ Send a Test Email</h2>
        <p style="font-size:.78rem;color:#9ca3af;margin-top:6px;margin-bottom:14px;">
            Send a test message to verify your SMTP settings are working. <strong style="color:var(--text);">Save your settings first</strong>, then enter any email address below.
        </p>

        <form id="testEmailForm" action="{{ url('/settings/test-email') }}" method="POST"
              style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
            @csrf
            <div style="flex:1;min-width:240px;">
                <label class="block text-sm font-medium mb-1">Recipient email</label>
                <input type="email" name="test_email" id="testEmailInput" required
                       value="{{ old('test_email') }}"
                       placeholder="your@email.com"
                       class="w-full border rounded-lg px-3 py-2 text-sm"
                       style="background:var(--input-bg);color:var(--text);border-color:var(--topbar-border);">
                @error('test_email')<p class="text-xs mt-1" style="color:#ef4444;">{{ $message }}</p>@enderror
            </div>
            <button type="submit" id="testEmailBtn"
                    class="px-5 py-2 rounded-lg font-semibold text-sm"
                    style="background:rgba(252,185,19,.15);color:#fcb913;border:1px solid #fcb913;white-space:nowrap;display:flex;align-items:center;gap:6px;">
                <svg id="testEmailSpinner" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                     style="display:none;animation:spin .8s linear infinite;">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                </svg>
                <span id="testEmailLabel">Send Test</span>
            </button>
        </form>

        <div style="margin-top:14px;padding:10px 14px;border-radius:8px;background:rgba(255,255,255,.03);border:1px solid var(--topbar-border);">
            <p style="font-size:.73rem;color:#6b7280;line-height:1.6;margin:0;">
                <strong style="color:#9ca3af;">Gmail tip:</strong> Use an <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:#fcb913;">App Password</a> (not your account password) — requires 2-Step Verification to be on.<br>
                <strong style="color:#9ca3af;">Other providers:</strong> Check your SMTP credentials in your email provider's dashboard.
            </p>
        </div>
    </div>

</div>

@php
$currencySymbolMap = ['USD'=>'$','ZWL'=>'ZWL','ZIG'=>'ZiG','ZAR'=>'R','BWP'=>'P','EUR'=>'€','GBP'=>'£','XAU'=>'Au'];
@endphp
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
function toggleMailPw() {
    const inp = document.getElementById('mailPassword');
    const eye    = document.getElementById('mailEye');
    const eyeOff = document.getElementById('mailEyeOff');
    if (inp.type === 'password') {
        inp.type = 'text'; eye.style.display = 'none'; eyeOff.style.display = '';
    } else {
        inp.type = 'password'; eye.style.display = ''; eyeOff.style.display = 'none';
    }
}

// Currency symbol — sync dropdown → symbol input + preview
const currencySymbols = @json($currencySymbolMap);
function syncCurrencySymbol(code) {
    const sym = currencySymbols[code] ?? code;
    document.getElementById('currencySymbolInput').value = sym;
    document.getElementById('currencyPreview').textContent = sym + '1,234.56';
}
// Update preview live when symbol is manually edited
document.getElementById('currencySymbolInput').addEventListener('input', function() {
    document.getElementById('currencyPreview').textContent = (this.value || '$') + '1,234.56';
});

// Test email: spinner + disable on submit
document.getElementById('testEmailForm').addEventListener('submit', function() {
    const btn     = document.getElementById('testEmailBtn');
    const spinner = document.getElementById('testEmailSpinner');
    const label   = document.getElementById('testEmailLabel');
    spinner.style.display = '';
    label.textContent = 'Sending…';
    btn.disabled = true;
    btn.style.opacity = '.7';
});

// Keyframe for spinner (inject once)
const style = document.createElement('style');
style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
document.head.appendChild(style);
</script>
@endpush
@endsection

