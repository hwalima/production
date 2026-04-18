<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Issue a new API token.
     * POST /api/auth/token
     * Body: { email, password, token_name? }
     */
    public function issue(Request $request)
    {
        $request->validate([
            'email'      => 'required|email|max:255',
            'password'   => 'required|string',
            'token_name' => 'nullable|string|max:100',
        ]);

        $user = User::where('email', $request->email)->first();

        // Use a single generic message to avoid user-enumeration
        if (!$user || !Hash::check($request->password, $user->password) || !$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect or the account is inactive.'],
            ]);
        }

        $tokenName = $request->input('token_name', 'api-token');
        $token     = $user->createToken($tokenName, ['read']);

        return response()->json([
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'token_name' => $tokenName,
        ], 201);
    }

    /**
     * Revoke the current access token.
     * DELETE /api/auth/token
     */
    public function revoke(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Token revoked successfully.']);
    }
}
