<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Load SMTP settings from the database at runtime so that
        // password resets, notifications, and exports all use the
        // credentials the admin has saved in Settings.
        try {
            if (Schema::hasTable('settings')) {
                $s = \App\Models\Setting::whereIn('key', [
                    'mail_host', 'mail_port', 'mail_username',
                    'mail_password', 'mail_encryption',
                    'mail_from_address', 'mail_from_name',
                ])->pluck('value', 'key');

                if ($s->get('mail_host')) {
                    config([
                        'mail.default'                 => 'smtp',
                        'mail.mailers.smtp.host'       => $s->get('mail_host'),
                        'mail.mailers.smtp.port'       => (int) ($s->get('mail_port') ?: 587),
                        'mail.mailers.smtp.username'   => $s->get('mail_username', ''),
                        'mail.mailers.smtp.password'   => $s->get('mail_password', ''),
                        'mail.mailers.smtp.encryption' => $s->get('mail_encryption') ?: null,
                        'mail.from.address'            => $s->get('mail_from_address') ?: config('mail.from.address'),
                        'mail.from.name'               => $s->get('mail_from_name')    ?: config('app.name'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Fail silently during migrations / fresh installs
        }

        // Share currency symbols with all views
        try {
            if (Schema::hasTable('settings')) {
                $currSym  = \App\Models\Setting::where('key', 'currency_symbol')->value('value');
                $currCode = \App\Models\Setting::where('key', 'currency_code')->value('value');
                View::share('currencySymbol', $currSym  ?: '$');
                View::share('currencyCode',   $currCode ?: 'USD');
            } else {
                View::share('currencySymbol', '$');
                View::share('currencyCode',   'USD');
            }
        } catch (\Throwable $e) {
            View::share('currencySymbol', '$');
            View::share('currencyCode',   'USD');
        }
    }
}
