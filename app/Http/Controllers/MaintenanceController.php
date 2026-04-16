<?php
namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\LoginLog;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class MaintenanceController extends Controller
{
    public function index()
    {
        $auditCount    = AuditLog::count();
        $auditOldest   = AuditLog::oldest()->value('created_at');
        $loginCount    = LoginLog::count();
        $loginOldest   = LoginLog::oldest()->value('created_at');

        $lastCacheClear = Setting::where('key', 'last_cache_clear')->value('value');

        return view('maintenance.index', compact(
            'auditCount', 'auditOldest',
            'loginCount', 'loginOldest',
            'lastCacheClear'
        ));
    }

    /* ── System cache ── */
    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Cache::forget('app_settings');

        Setting::updateOrCreate(['key' => 'last_cache_clear'], ['value' => now()->toDateTimeString()]);

        return back()->with('success', 'All caches cleared successfully.');
    }

    /* ── Audit logs ── */
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($q = $request->input('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('user_name', 'like', "%{$q}%")
                   ->orWhere('action', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%");
            });
        }
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('maintenance.audit-logs', compact('logs'));
    }

    public function purgeAuditLogs(Request $request)
    {
        $days = (int) $request->input('days', 90);

        if ($days === 0) {
            $deleted = AuditLog::count();
            AuditLog::truncate();
            $label = 'all';
        } else {
            $cutoff = Carbon::now()->subDays($days);
            $deleted = AuditLog::where('created_at', '<', $cutoff)->delete();
            $label = "older than {$days} days";
        }

        if ($deleted > 0) {
            AuditLog::record('deleted', "Purged {$deleted} audit log entries ({$label}).");
        }

        return back()->with('success', "Deleted {$deleted} audit log entr" . ($deleted === 1 ? 'y' : 'ies') . " ({$label}).");
    }

    /* ── Login logs ── */
    public function loginLogs(Request $request)
    {
        $query = LoginLog::with('user')->latest();

        if ($q = $request->input('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('user_name', 'like', "%{$q}%")
                   ->orWhere('user_email', 'like', "%{$q}%");
            });
        }
        if ($event = $request->input('event')) {
            $query->where('event', $event);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('maintenance.login-logs', compact('logs'));
    }

    public function purgeLoginLogs(Request $request)
    {
        $days = (int) $request->input('days', 90);

        if ($days === 0) {
            $deleted = LoginLog::count();
            LoginLog::truncate();
            $label = 'all';
        } else {
            $cutoff = Carbon::now()->subDays($days);
            $deleted = LoginLog::where('created_at', '<', $cutoff)->delete();
            $label = "older than {$days} days";
        }

        AuditLog::record('deleted', "Purged {$deleted} login log entries ({$label}).");

        return back()->with('success', "Deleted {$deleted} login log entr" . ($deleted === 1 ? 'y' : 'ies') . " ({$label}).");
    }
}
