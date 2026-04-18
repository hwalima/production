<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    /**
     * Show the API token management page.
     */
    public function index(Request $request)
    {
        $tokens = $request->user()->tokens()
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'last_used_at', 'created_at']);

        return view('profile.api-tokens', compact('tokens'));
    }

    /**
     * Create a new personal access token.
     * The plaintext token is flashed once and never stored.
     */
    public function store(Request $request)
    {
        $request->validate([
            'token_name' => 'required|string|max:100',
        ]);

        $token = $request->user()->createToken(
            $request->token_name,
            ['read']
        );

        return redirect()->route('api-tokens.index')
            ->with('new_token',  $token->plainTextToken)
            ->with('token_name', $request->token_name);
    }

    /**
     * Revoke (delete) a specific token belonging to the authenticated user.
     */
    public function destroy(Request $request, int $tokenId)
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return redirect()->route('api-tokens.index')
            ->with('success', 'API token revoked successfully.');
    }
}
