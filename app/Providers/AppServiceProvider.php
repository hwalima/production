<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
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
        // Load all settings once per request, cached for 10 minutes.
        // Cache is invalidated in SettingsController::update().
        try {
            if (Schema::hasTable('settings')) {
                $s = Cache::remember('app_settings', 600, fn () =>
                    \App\Models\Setting::all()->pluck('value', 'key')
                );

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

                View::share('currencySymbol', $s->get('currency_symbol') ?: '$');
                View::share('currencyCode',   $s->get('currency_code')   ?: 'USD');
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
