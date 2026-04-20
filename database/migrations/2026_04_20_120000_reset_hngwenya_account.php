<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Resets hngwenya@trukumbmining.africa:
 *  - Temporary password: Epoch@2026  (they will be prompted to change it on first login)
 *  - Clears any stuck 2FA state
 *  - Ensures is_active = true
 *  - Sets force_password_change = true so they set their own password on login
 */
return new class extends Migration {
    public function up(): void
    {
        DB::table('users')
            ->where('email', 'hngwenya@trukumbmining.africa')
            ->update([
                'password'                  => Hash::make('Epoch@2026'),
                'force_password_change'     => true,
                'is_active'                 => true,
                'two_factor_secret'         => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at'   => null,
                'updated_at'                => now(),
            ]);
    }

    public function down(): void
    {
        // non-destructive — no rollback needed
    }
};
