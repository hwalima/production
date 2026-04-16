<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginLog extends Model
{
    protected $fillable = [
        'user_id', 'user_name', 'user_email', 'ip_address', 'user_agent', 'event',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $event, $user = null): void
    {
        $user = $user ?? auth()->user();
        static::create([
            'user_id'    => $user?->id,
            'user_name'  => $user?->name,
            'user_email' => $user?->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'event'      => $event,
        ]);
    }
}
