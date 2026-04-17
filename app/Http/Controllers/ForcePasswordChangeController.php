<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ForcePasswordChangeController extends Controller
{
    public function show()
    {
        // If they don't need to change their password, redirect to dashboard
        if (!auth()->user()->force_password_change) {
            return redirect()->route('dashboard');
        }

        return view('auth.force-password-change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = auth()->user();

        $user->update([
            'password'              => Hash::make($request->password),
            'force_password_change' => false,
        ]);

        return redirect()->route('dashboard')->with('success', 'Password updated successfully. Welcome!');
    }
}
