<x-guest-layout>

    {{-- Session status --}}
    @if(session('status'))
        <div class="alert-info">{{ session('status') }}</div>
    @endif

    <div class="card-title">Welcome back</div>
    <div class="card-sub">Sign in to your account to continue</div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email --}}
        <div class="field">
            <label for="email">Email Address</label>
            <div class="field-wrap">
                <input id="email" type="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="you@example.com"
                       required autofocus autocomplete="username">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="field">
            <label for="password">Password</label>
            <div class="field-wrap">
                <input id="password" type="password" name="password"
                       placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                       class="pw-input"
                       required autocomplete="current-password">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <button type="button" class="pw-toggle" onclick="togglePw()" title="Show/hide password">
                    <svg id="pwEye" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-.274.857-.68 1.657-1.193 2.374"/></svg>
                    <svg id="pwEyeOff" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.293-3.95M9.878 9.878A3 3 0 0114.12 14.12M3 3l18 18"/></svg>
                </button>
            </div>
            @error('password')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Remember / Forgot --}}
        <div class="form-row">
            <label class="remember-wrap">
                <input type="checkbox" name="remember" id="remember_me">
                <span>Remember me</span>
            </label>
            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="btn-login">Sign In</button>
    </form>

    <script>
        function togglePw() {
            var inp = document.getElementById('password');
            var eye = document.getElementById('pwEye');
            var off = document.getElementById('pwEyeOff');
            if (inp.type === 'password') {
                inp.type = 'text';
                eye.style.display = 'none';
                off.style.display = 'block';
            } else {
                inp.type = 'password';
                eye.style.display = 'block';
                off.style.display = 'none';
            }
        }
    </script>

</x-guest-layout>
