<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Models\LoginLog;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    public function boot()
    {
        Event::listen(Login::class, function (Login $e) {
            LoginLog::record('login', $e->user);
        });
        Event::listen(Logout::class, function (Logout $e) {
            LoginLog::record('logout', $e->user);
        });
        Event::listen(Failed::class, function (Failed $e) {
            LoginLog::create([
                'user_id'    => $e->user?->id,
                'user_name'  => $e->user?->name,
                'user_email' => $e->credentials['email'] ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'event'      => 'failed',
            ]);
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
