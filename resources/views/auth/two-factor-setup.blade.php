@extends('layouts.app')
@section('page-title', 'Set Up Two-Factor Authentication')

@push('styles')
<style>
.tfa-wrap { max-width:600px; margin:0 auto; }
.tfa-card { background:var(--card); border-radius:16px; padding:28px 28px; box-shadow:0 2px 12px rgba(0,0,0,.07); margin-bottom:24px; }
.tfa-step-label { display:inline-flex; align-items:center; gap:8px; font-size:.78rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#fcb913; margin-bottom:14px; }
.tfa-step-label svg { width:16px; height:16px; }
.tfa-title { font-size:1.15rem; font-weight:800; color:var(--text); margin-bottom:6px; }
.tfa-desc  { font-size:.85rem; color:#9ca3af; margin-bottom:24px; line-height:1.55; }

/* QR container */
.qr-box { display:flex; justify-content:center; margin-bottom:20px; }
.qr-box canvas, .qr-box img { border-radius:12px; padding:12px; background:#fff; box-shadow:0 4px 16px rgba(0,0,0,.12); }

/* Manual secret */
.secret-box {
    background:var(--input-bg); border:1.5px dashed var(--topbar-border);
    border-radius:10px; padding:12px 16px; text-align:center;
    font-family:monospace; letter-spacing:.15em; font-size:.95rem;
    color:var(--text); cursor:pointer; user-select:all; margin-bottom:20px;
    word-break:break-all;
}
.secret-box:hover { border-color:#fcb913; }

/* Recovery codes grid */
.codes-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin:16px 0 24px; }
.code-pill  { background:var(--input-bg); border:1px solid var(--topbar-border); border-radius:8px; padding:8px 12px; font-family:monospace; font-size:.88rem; letter-spacing:.08em; color:var(--text); text-align:center; }

/* OTP input */
.otp-input {
    background:var(--input-bg); border:1.5px solid var(--topbar-border);
    color:var(--text); border-radius:10px; padding:12px 16px;
    font-size:1.4rem; letter-spacing:.3em; text-align:center;
    width:100%; outline:none; transition:border-color .2s;
}
.otp-input:focus { border-color:#fcb913; box-shadow:0 0 0 3px rgba(252,185,19,.15); }
.field-error { font-size:.75rem; color:#ef4444; margin-top:4px; }

.btn-primary {
    display:inline-flex; align-items:center; gap:8px;
    background:#fcb913; color:#001a4d; font-weight:700;
    border:none; border-radius:10px; padding:11px 24px;
    font-size:.875rem; cursor:pointer; transition:background .15s, transform .1s;
}
.btn-primary:hover { background:#db9f01; transform:translateY(-1px); }

.btn-outline {
    display:inline-flex; align-items:center; gap:8px;
    background:none; color:var(--text); font-weight:600;
    border:1.5px solid var(--topbar-border); border-radius:10px;
    padding:10px 20px; font-size:.875rem; cursor:pointer;
    transition:border-color .15s;
}
.btn-outline:hover { border-color:#fcb913; }

.success-badge {
    display:inline-flex; align-items:center; gap:6px;
    background:#dcfce7; color:#166534; border-radius:8px;
    padding:8px 16px; font-size:.85rem; font-weight:600;
}
.warning-box {
    background:#fefce8; border:1px solid #fde68a; border-radius:10px;
    padding:14px 16px; font-size:.82rem; color:#713f12; margin-bottom:20px;
    line-height:1.55;
}
</style>
@endpush

@section('content')
<div class="page-header" style="margin-bottom:28px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:800;color:var(--text);">Two-Factor Authentication</h1>
        <p style="color:#9ca3af;font-size:.85rem;margin-top:2px;">
            Secure your account with a TOTP authenticator app
        </p>
    </div>
    <a href="{{ route('profile.edit') }}" class="btn-outline">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Profile
    </a>
</div>

<div class="tfa-wrap">

@if($step === 'scan')

    {{-- ── Step 1: Scan QR ── --}}
    <div class="tfa-card">
        <div class="tfa-step-label">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 3.5a3.5 3.5 0 11-7 0 3.5 3.5 0 017 0z"/>
            </svg>
            Step 1 of 2 &mdash; Scan QR Code
        </div>
        <div class="tfa-title">Open your authenticator app and scan this code</div>
        <div class="tfa-desc">
            Use <strong>Google Authenticator</strong>, <strong>Authy</strong>, <strong>Microsoft Authenticator</strong>
            or any TOTP-compatible app. Tap the + icon to add a new account, then scan the QR code below.
        </div>

        {{-- QR Code --}}
        <div class="qr-box">
            <canvas id="qrCanvas"></canvas>
        </div>

        {{-- Manual entry fallback --}}
        <p style="font-size:.78rem;text-align:center;color:#9ca3af;margin-bottom:8px;">
            Can't scan? Enter this secret key manually:
        </p>
        <div class="secret-box" onclick="copySecret(this)" title="Click to copy">
            {{ chunk_split($secret, 4, ' ') }}
        </div>
        <p style="font-size:.73rem;color:#9ca3af;text-align:center;margin-bottom:24px;" id="copyHint">
            Click to copy &bull; Account type: Time-based (TOTP)
        </p>
    </div>

    {{-- ── Step 2: Verify first code ── --}}
    <div class="tfa-card">
        <div class="tfa-step-label">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Step 2 of 2 &mdash; Verify Code
        </div>
        <div class="tfa-title">Enter the 6-digit code to confirm setup</div>
        <div class="tfa-desc">
            After scanning, your app will display a 6-digit code that refreshes every 30 seconds.
            Enter it below to activate 2FA.
        </div>

        <form method="POST" action="{{ route('two-factor.confirm') }}">
            @csrf
            <input type="hidden" name="_method" value="POST">

            <input
                type="text" name="code"
                class="otp-input"
                inputmode="numeric" pattern="\d{6}" maxlength="6"
                placeholder="000 000"
                autocomplete="one-time-code"
                autofocus
                value="{{ old('code') }}">

            @error('code')
                <div class="field-error">{{ $message }}</div>
            @enderror

            <div style="display:flex;gap:12px;margin-top:20px;align-items:center;">
                <button type="submit" class="btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Activate 2FA
                </button>
                <a href="{{ route('profile.edit') }}" style="font-size:.82rem;color:#9ca3af;">Cancel</a>
            </div>
        </form>
    </div>

    {{-- QR Code JS --}}
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
    QRCode.toCanvas(
        document.getElementById('qrCanvas'),
        @json($qrCodeUri),
        { width: 220, margin: 2, color: { dark: '#000000', light: '#ffffff' } },
        function(err) {
            if (err) {
                document.getElementById('qrCanvas').style.display = 'none';
            }
        }
    );

    function copySecret(el) {
        const raw = el.textContent.replace(/\s/g, '');
        navigator.clipboard.writeText(raw).then(() => {
            const hint = document.getElementById('copyHint');
            hint.textContent = '✓ Copied to clipboard';
            hint.style.color = '#22c55e';
            setTimeout(() => {
                hint.textContent = 'Click to copy · Account type: Time-based (TOTP)';
                hint.style.color = '';
            }, 2500);
        });
    }
    </script>

@elseif($step === 'complete')

    {{-- ── Success: show recovery codes ── --}}
    <div class="tfa-card">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:44px;height:44px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#16a34a">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <div class="tfa-title" style="margin-bottom:0;">2FA is now active!</div>
                <div style="font-size:.82rem;color:#9ca3af;">Your account is now protected with two-factor authentication.</div>
            </div>
        </div>

        <div class="warning-box">
            <strong>⚠ Save these recovery codes now.</strong> They are shown only once and cannot be retrieved later.
            Each code can only be used once. Store them in a secure place (password manager, printed copy, etc.).
            If you lose access to your authenticator app, a recovery code is your only way back in.
        </div>

        <div class="tfa-title" style="font-size:.95rem;margin-bottom:12px;">Recovery Codes</div>
        <div class="codes-grid">
            @foreach($recoveryCodes as $code)
                <div class="code-pill">{{ $code }}</div>
            @endforeach
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
            <button onclick="downloadCodes()" class="btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download codes
            </button>
            <a href="{{ route('profile.edit') }}" class="btn-primary">
                Done — Go to Profile
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>

    <script>
    function downloadCodes() {
        const codes = @json($recoveryCodes ?? []);
        const content = "{{ config('app.name') }} — 2FA Recovery Codes\n" +
                        "Generated: " + new Date().toLocaleDateString() + "\n\n" +
                        codes.join("\n") +
                        "\n\nStore these securely. Each code can only be used once.";
        const blob = new Blob([content], { type: 'text/plain' });
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = 'recovery-codes.txt';
        a.click();
        URL.revokeObjectURL(url);
    }
    </script>

@endif

</div>
@endsection
