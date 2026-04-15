<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'My Mine') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $s       = \App\Models\Setting::all()->pluck('value','key');
        $appName = $s['company_name']    ?? config('app.name','My Mine');
        $tagline = $s['company_location'] ?? 'Mine Production Management';
        $logoP   = $s['logo_path'] ?? '';
        $logoUrl = $logoP ? asset('storage/'.$logoP) : null;
    @endphp
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Figtree', sans-serif;
            min-height: 100vh;
            background: #000d24;
            display: flex;
            overflow-x: hidden;
        }

        /* ── Animated background ── */
        .bg-scene {
            position: fixed; inset: 0; z-index: 0;
            background: linear-gradient(135deg, #000d24 0%, #001a4d 50%, #00112e 100%);
            overflow: hidden;
        }
        .bg-scene::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 50%, rgba(252,185,19,.13) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 80% 20%, rgba(252,185,19,.08) 0%, transparent 55%),
                radial-gradient(ellipse 40% 40% at 60% 80%, rgba(0,90,200,.15) 0%, transparent 50%);
            animation: bgShift 12s ease-in-out infinite alternate;
        }
        @keyframes bgShift {
            0%   { opacity: 1; transform: scale(1); }
            100% { opacity: .85; transform: scale(1.06); }
        }

        /* Floating particles */
        .particles { position: absolute; inset: 0; overflow: hidden; }
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(252,185,19,.18);
            animation: float linear infinite;
        }
        @keyframes float {
            0%   { transform: translateY(100vh) scale(0); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: .6; }
            100% { transform: translateY(-10vh) scale(1.2); opacity: 0; }
        }

        /* Gold grid overlay */
        .bg-grid {
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(252,185,19,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(252,185,19,.04) 1px, transparent 1px);
            background-size: 60px 60px;
        }

        /* ── Left panel (branding) ── */
        .brand-panel {
            display: none;
            flex: 1;
            position: relative;
            z-index: 1;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 60px 64px;
        }
        @media (min-width: 1024px) { .brand-panel { display: flex; } }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 48px;
        }
        .brand-logo-img {
            width: 64px; height: 64px;
            border-radius: 14px;
            background: rgba(255,255,255,.1);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
            box-shadow: 0 0 0 2px rgba(252,185,19,.4), 0 8px 32px rgba(0,0,0,.4);
            backdrop-filter: blur(8px);
        }
        .brand-logo-img img { width: 56px; height: 56px; object-fit: contain; }
        .brand-logo-text {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fcb913;
            letter-spacing: -.02em;
            line-height: 1;
        }
        .brand-logo-sub {
            font-size: .8rem;
            color: rgba(255,255,255,.5);
            margin-top: 4px;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .brand-headline {
            font-size: clamp(2rem, 3.5vw, 3rem);
            font-weight: 800;
            color: #fff;
            line-height: 1.15;
            margin-bottom: 20px;
            letter-spacing: -.02em;
        }
        .brand-headline span { color: #fcb913; }
        .brand-tagline {
            font-size: 1rem;
            color: rgba(255,255,255,.55);
            max-width: 360px;
            line-height: 1.7;
            margin-bottom: 48px;
        }

        .brand-stats {
            display: flex;
            gap: 32px;
        }
        .stat {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .stat-value {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fcb913;
            line-height: 1;
        }
        .stat-label {
            font-size: .7rem;
            color: rgba(255,255,255,.45);
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        /* Divider line */
        .brand-divider {
            width: 1px;
            background: linear-gradient(to bottom, transparent, rgba(252,185,19,.3) 30%, rgba(252,185,19,.3) 70%, transparent);
            position: absolute;
            right: 0; top: 10%; bottom: 10%;
        }

        /* ── Right panel (form) ── */
        .form-panel {
            width: 100%;
            max-width: 480px;
            min-height: 100vh;
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 32px;
        }
        @media (min-width: 1024px) { .form-panel { padding: 60px 56px; } }

        /* Card */
        .login-card {
            width: 100%;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(252,185,19,.15);
            border-radius: 24px;
            padding: 40px 36px;
            backdrop-filter: blur(20px);
            box-shadow:
                0 0 0 1px rgba(252,185,19,.08),
                0 32px 64px rgba(0,0,0,.5),
                inset 0 1px 0 rgba(255,255,255,.08);
            animation: cardIn .6s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(32px) scale(.97); }
            to   { opacity: 1; transform: none; }
        }

        /* Mobile-only logo */
        .mobile-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 32px;
            animation: cardIn .5s cubic-bezier(.16,1,.3,1) both;
        }
        @media (min-width: 1024px) { .mobile-brand { display: none; } }
        .mobile-brand-logo {
            width: 72px; height: 72px;
            border-radius: 18px;
            background: rgba(252,185,19,.12);
            border: 1px solid rgba(252,185,19,.3);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
            margin-bottom: 14px;
            box-shadow: 0 8px 32px rgba(0,0,0,.4);
        }
        .mobile-brand-logo img { width: 60px; height: 60px; object-fit: contain; }
        .mobile-brand-name { font-size: 1.5rem; font-weight: 800; color: #fcb913; }
        .mobile-brand-sub  { font-size: .75rem; color: rgba(255,255,255,.45); margin-top: 4px; text-transform: uppercase; letter-spacing: .08em; }

        /* Form elements */
        .card-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 6px;
        }
        .card-sub {
            font-size: .85rem;
            color: rgba(255,255,255,.45);
            margin-bottom: 28px;
        }

        .field { margin-bottom: 18px; }
        .field label {
            display: block;
            font-size: .78rem;
            font-weight: 600;
            color: rgba(255,255,255,.6);
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: 7px;
        }
        .field-wrap { position: relative; display: flex; align-items: center; }
        .field-wrap > svg {
            position: absolute;
            left: 14px;
            width: 16px; height: 16px;
            color: rgba(255,255,255,.3);
            pointer-events: none;
            transition: color .2s;
            z-index: 1;
        }
        .field input {
            width: 100%;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 12px;
            padding: 12px 14px 12px 42px;
            color: #fff;
            font-size: .9rem;
            font-family: inherit;
            outline: none;
            transition: border-color .2s, background .2s, box-shadow .2s;
        }
        .field input::placeholder { color: rgba(255,255,255,.25); }
        .field input:focus {
            border-color: #fcb913;
            background: rgba(252,185,19,.06);
            box-shadow: 0 0 0 3px rgba(252,185,19,.12);
        }
        .field input:focus + svg,
        .field-wrap:focus-within svg { color: #fcb913; }
        .field-error {
            font-size: .78rem;
            color: #f87171;
            margin-top: 5px;
        }

        /* Password toggle */
        .pw-toggle {
            position: absolute;
            right: 12px;
            background: none; border: none; cursor: pointer;
            color: rgba(255,255,255,.3);
            padding: 4px;
            transition: color .2s;
            display: flex; align-items: center; justify-content: center;
            z-index: 1;
        }
        .pw-toggle:hover { color: #fcb913; }
        .pw-input { padding-right: 44px !important; }

        /* Row */
        .form-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            gap: 8px;
        }
        .remember-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        .remember-wrap input[type=checkbox] {
            width: 16px; height: 16px;
            accent-color: #fcb913;
            cursor: pointer;
        }
        .remember-wrap span { font-size: .82rem; color: rgba(255,255,255,.5); }
        .forgot-link {
            font-size: .82rem;
            color: rgba(252,185,19,.8);
            text-decoration: none;
            transition: color .2s;
            white-space: nowrap;
        }
        .forgot-link:hover { color: #fcb913; }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #fcb913 0%, #e8a800 100%);
            color: #001a4d;
            font-size: .95rem;
            font-weight: 700;
            font-family: inherit;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            letter-spacing: .02em;
            transition: transform .15s, box-shadow .15s, filter .15s;
            box-shadow: 0 4px 20px rgba(252,185,19,.3);
            position: relative;
            overflow: hidden;
        }
        .btn-login::after {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,.18) 0%, transparent 60%);
            border-radius: inherit;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(252,185,19,.4);
            filter: brightness(1.06);
        }
        .btn-login:active { transform: translateY(0); }

        /* Status / error alert */
        .alert-info {
            background: rgba(252,185,19,.1);
            border: 1px solid rgba(252,185,19,.25);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: .82rem;
            color: #fcb913;
            margin-bottom: 18px;
        }

        /* Footer */
        .form-footer {
            margin-top: 20px;
            text-align: center;
            font-size: .75rem;
            color: rgba(255,255,255,.25);
        }
    </style>
</head>
<body>

{{-- Animated background --}}
<div class="bg-scene">
    <div class="bg-grid"></div>
    <div class="particles" id="particles"></div>
</div>

{{-- Left branding panel --}}
<div class="brand-panel">
    <div class="brand-logo">
        <div class="brand-logo-img">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $appName }}">
            @else
                <span style="font-size:2rem;">&#9968;</span>
            @endif
        </div>
        <div>
            <div class="brand-logo-text">{{ $appName }}</div>
            <div class="brand-logo-sub">{{ $tagline }}</div>
        </div>
    </div>

    <h1 class="brand-headline">
        Manage Your<br>
        <span>Mining Operations</span><br>
        With Precision
    </h1>
    <p class="brand-tagline">
        Real-time production tracking, consumables management, assay results and comprehensive reporting — all in one secure platform.
    </p>

    <div class="brand-stats">
        <div class="stat"><div class="stat-value">6+</div><div class="stat-label">Modules</div></div>
        <div class="stat"><div class="stat-value">100%</div><div class="stat-label">Secure</div></div>
        <div class="stat"><div class="stat-value">24/7</div><div class="stat-label">Access</div></div>
    </div>

    <div class="brand-divider"></div>
</div>

{{-- Right form panel --}}
<div class="form-panel" style="margin-left:auto;">

    {{-- Mobile logo --}}
    <div class="mobile-brand">
        <div class="mobile-brand-logo">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $appName }}">
            @else
                <span style="font-size:2rem;color:#fcb913;">&#9968;</span>
            @endif
        </div>
        <div class="mobile-brand-name">{{ $appName }}</div>
        <div class="mobile-brand-sub">{{ $tagline }}</div>
    </div>

    <div class="login-card">
        {{ $slot }}
    </div>

    <p class="form-footer">&copy; {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
</div>

<script>
    // Generate floating particles
    (function(){
        var container = document.getElementById('particles');
        for (var i = 0; i < 18; i++) {
            var p = document.createElement('div');
            p.className = 'particle';
            var size = Math.random() * 6 + 3;
            p.style.cssText = [
                'width:'  + size + 'px',
                'height:' + size + 'px',
                'left:'   + Math.random() * 100 + '%',
                'animation-duration:' + (Math.random() * 14 + 8) + 's',
                'animation-delay:' + (Math.random() * -20) + 's',
                'opacity:' + (Math.random() * .5 + .1),
            ].join(';');
            container.appendChild(p);
        }
    }());
</script>
</body>
</html>
