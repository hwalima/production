<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ThemeController extends Controller
{
    /**
     * Persist the user's theme preference.
     * PATCH /user/theme
     * Body: { theme: 'light'|'dark' }
     * Returns JSON — called via fetch() from the dark-mode toggle.
     */
    public function update(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:light,dark',
        ]);

        $request->user()->update(['theme_preference' => $request->theme]);

        return response()->json(['theme' => $request->theme]);
    }
}
