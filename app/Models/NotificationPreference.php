<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $fillable = ['user_id', 'alert_type', 'enabled'];

    protected $casts = ['enabled' => 'boolean'];

    /* ── Alert type registry ──────────────────────────────────────────────
     * Each entry: 'key' => ['label', 'description', 'roles_that_receive_it']
     * roles_that_receive_it: null means all active users
     */
    public const ALERT_TYPES = [
        'consumable_low_stock' => [
            'label'       => 'Low Stock Alert',
            'description' => 'Email when a consumable item falls below its reorder level.',
            'roles'       => null,  // sent to all users
        ],
        'overdue_action_items' => [
            'label'       => 'Overdue Action Items Digest',
            'description' => 'Daily email digest of overdue action items.',
            'roles'       => ['super_admin', 'admin', 'manager'],
        ],
        'machine_service_alert' => [
            'label'       => 'Machine Service Alert',
            'description' => 'Email when a machine becomes overdue for service.',
            'roles'       => ['super_admin', 'admin'],
        ],
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check whether $user has opted IN for the given alert type.
     * Defaults to true (opted in) if no preference row exists.
     */
    public static function wantsAlert(User $user, string $alertType): bool
    {
        $pref = static::where('user_id', $user->id)
                      ->where('alert_type', $alertType)
                      ->first();

        return $pref ? $pref->enabled : true;
    }

    /**
     * Upsert a preference for a user.
     */
    public static function setPreference(int $userId, string $alertType, bool $enabled): void
    {
        static::updateOrCreate(
            ['user_id' => $userId, 'alert_type' => $alertType],
            ['enabled'  => $enabled]
        );
    }
}
