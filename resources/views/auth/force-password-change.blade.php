<x-guest-layout>

    <div class="card-title">Set Your Password</div>
    <div class="card-sub">For your security, please choose a new password before continuing.</div>

    <div style="background:rgba(252,185,19,.1);border:1px solid rgba(252,185,19,.3);border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:.82rem;color:#fcb913;line-height:1.5;">
        🔐 Your account was created with a temporary password. Please set a permanent password now.
    </div>

    @if ($errors->any())
        <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:.82rem;color:#ef4444;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.force-change.update') }}">
        @csrf

        <div class="field">
            <label for="password">New Password</label>
            <div class="field-wrap">
                <input id="password" type="password" name="password"
                       placeholder="Min 8 chars, letters &amp; numbers"
                       class="pw-input"
                       required autofocus autocomplete="new-password">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <button type="button" class="pw-toggle" onclick="togglePw('password','pwEye','pwEyeOff')" title="Show/hide password">
                    <svg id="pwEye" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-.274.857-.68 1.657-1.193 2.374"/></svg>
                    <svg id="pwEyeOff" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.293-3.95M9.878 9.878A3 3 0 0114.12 14.12M3 3l18 18"/></svg>
                </button>
            </div>
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm Password</label>
            <div class="field-wrap">
                <input id="password_confirmation" type="password" name="password_confirmation"
                       placeholder="Re-enter your new password"
                       class="pw-input"
                       required autocomplete="new-password">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <button type="button" class="pw-toggle" onclick="togglePw('password_confirmation','pwEye2','pwEyeOff2')" title="Show/hide password">
                    <svg id="pwEye2" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-.274.857-.68 1.657-1.193 2.374"/></svg>
                    <svg id="pwEyeOff2" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.293-3.95M9.878 9.878A3 3 0 0114.12 14.12M3 3l18 18"/></svg>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-signin">Set Password &amp; Continue →</button>
    </form>

    <form method="POST" action="{{ route('logout') }}" style="margin-top:16px;text-align:center;">
        @csrf
        <button type="submit" style="background:none;border:none;font-size:.78rem;color:#9ca3af;cursor:pointer;text-decoration:underline;">
            Sign out and log in as a different user
        </button>
    </form>

    <script>
    function togglePw(fieldId, eyeId, eyeOffId) {
        var input = document.getElementById(fieldId);
        var eye = document.getElementById(eyeId);
        var eyeOff = document.getElementById(eyeOffId);
        if (input.type === 'password') {
            input.type = 'text';
            eye.style.display = 'none';
            eyeOff.style.display = '';
        } else {
            input.type = 'password';
            eye.style.display = '';
            eyeOff.style.display = 'none';
        }
    }
    </script>

</x-guest-layout>
