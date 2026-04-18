<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationPreferencesController extends Controller
{
    /* ── GET /profile/notification-preferences
       Any authenticated user — edit their own prefs              */
    public function edit()
    {
        $user  = auth()->user();
        $types = NotificationPreference::ALERT_TYPES;

        // Build a keyed map of current prefs (default true if missing)
        $prefs = [];
        foreach ($types as $key => $meta) {
            // Only show alert types relevant to this user's role
            if ($meta['roles'] !== null && !in_array($user->role, $meta['roles'], true)) {
                continue;
            }
            $pref         = $user->notificationPreferences->firstWhere('alert_type', $key);
            $prefs[$key]  = $pref ? $pref->enabled : true;
        }

        return view('profile.notification-preferences', compact('prefs', 'types'));
    }

    /* ── PATCH /profile/notification-preferences  */
    public function update(Request $request)
    {
        $user  = auth()->user();
        $types = NotificationPreference::ALERT_TYPES;

        foreach ($types as $key => $meta) {
            if ($meta['roles'] !== null && !in_array($user->role, $meta['roles'], true)) {
                continue;
            }
            $enabled = $request->boolean("prefs.{$key}", false);
            NotificationPreference::setPreference($user->id, $key, $enabled);
        }

        AuditLog::record(
            'notification_prefs_updated',
            "User {$user->name} updated their notification preferences",
            'User',
            $user->id
        );

        return back()->with('success', 'Notification preferences saved.');
    }

    /* ── GET /admin/notification-preferences
       Super-admin only — see who opted out of what               */
    public function adminOverview()
    {
        $types = NotificationPreference::ALERT_TYPES;

        // For each alert type, get every user who is eligible and their opt-in status
        $overview = [];
        foreach ($types as $key => $meta) {
            $query = User::where('is_active', true)->orderBy('name');
            if ($meta['roles'] !== null) {
                $query->whereIn('role', $meta['roles']);
            }
            $users = $query->with(['notificationPreferences' => fn ($q) => $q->where('alert_type', $key)])->get();

            $rows = $users->map(function ($u) use ($key) {
                $pref    = $u->notificationPreferences->first();
                $enabled = $pref ? $pref->enabled : true;   // default = opted in
                return [
                    'user'    => $u,
                    'enabled' => $enabled,
                ];
            });

            $overview[$key] = [
                'meta'      => $meta,
                'rows'      => $rows,
                'opted_out' => $rows->where('enabled', false)->count(),
            ];
        }

        return view('admin.notification-preferences', compact('overview', 'types'));
    }
}
