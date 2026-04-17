<x-guest-layout>

<div class="card-title">Two-Factor Authentication</div>
<div class="card-sub">Enter the 6-digit code from your authenticator app to continue.</div>

@if(session('status'))
    <div class="alert-info">{{ session('status') }}</div>
@endif

{{-- ── OTP form ── --}}
<div id="totpForm">
    <form method="POST" action="{{ route('two-factor.challenge.verify') }}">
        @csrf

        <div class="field">
            <label for="code">Authentication Code</label>
            <div class="field-wrap">
                <input id="code" type="text" name="code"
                       inputmode="numeric" pattern="\d{6}" maxlength="6"
                       placeholder="000 000"
                       autocomplete="one-time-code"
                       autofocus
                       style="letter-spacing:.25em; font-size:1.2rem; text-align:center;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            @error('code')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn-submit" style="width:100%;margin-top:8px;">
            Verify &amp; Sign In
        </button>
    </form>

    <div style="margin-top:20px; text-align:center;">
        <button type="button"
                onclick="toggleRecovery()"
                style="background:none;border:none;color:#9ca3af;font-size:.82rem;cursor:pointer;text-decoration:underline;">
            Use a recovery code instead
        </button>
    </div>
</div>

{{-- ── Recovery code form ── --}}
<div id="recoveryForm" style="display:none;">
    <form method="POST" action="{{ route('two-factor.challenge.verify') }}">
        @csrf

        <div class="field">
            <label for="recovery_code">Recovery Code</label>
            <div class="field-wrap">
                <input id="recovery_code" type="text" name="recovery_code"
                       placeholder="XXXXX-XXXXX"
                       autocomplete="off"
                       style="letter-spacing:.1em; font-size:.95rem; text-transform:uppercase;"
                       oninput="this.value = this.value.toUpperCase()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            @error('recovery_code')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn-submit" style="width:100%;margin-top:8px;">
            Sign In with Recovery Code
        </button>
    </form>

    <div style="margin-top:20px; text-align:center;">
        <button type="button"
                onclick="toggleRecovery()"
                style="background:none;border:none;color:#9ca3af;font-size:.82rem;cursor:pointer;text-decoration:underline;">
            Use authenticator app instead
        </button>
    </div>
</div>

{{-- ── Logout link ── --}}
<div style="margin-top:28px; text-align:center; border-top:1px solid rgba(255,255,255,.08); padding-top:20px;">
    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
        @csrf
        <button type="submit"
                style="background:none;border:none;color:#9ca3af;font-size:.8rem;cursor:pointer;">
            Not you? Sign out
        </button>
    </form>
</div>

<script>
function toggleRecovery() {
    const totp     = document.getElementById('totpForm');
    const recovery = document.getElementById('recoveryForm');
    const show     = recovery.style.display === 'none';
    totp.style.display     = show ? 'none' : '';
    recovery.style.display = show ? ''     : 'none';
    if (show) document.getElementById('recovery_code').focus();
    else      document.getElementById('code').focus();
}

// Auto-show recovery form if there was a recovery_code error
@if(session('show_recovery') || $errors->has('recovery_code'))
    document.addEventListener('DOMContentLoaded', () => toggleRecovery());
@endif
</script>

</x-guest-layout>
