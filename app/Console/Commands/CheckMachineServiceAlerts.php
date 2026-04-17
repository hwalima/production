<?php

namespace App\Console\Commands;

use App\Mail\MachineServiceAlert;
use App\Models\MachineRuntime;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckMachineServiceAlerts extends Command
{
    protected $signature   = 'machines:check-service-alerts';
    protected $description = 'Email admins when machines become overdue for service (fires once per overdue event)';

    public function handle(): int
    {
        // ── Find the latest runtime record per machine_code ────────────────
        // A machine is "newly overdue" when its most-recent record has
        // next_service_date < today AND service_alert_sent_at IS NULL.
        $today = Carbon::today();

        // Get the ID of the most-recent record for each machine_code
        $latestIds = MachineRuntime::selectRaw('MAX(id) as id')
            ->groupBy('machine_code')
            ->pluck('id');

        $newlyOverdue = MachineRuntime::whereIn('id', $latestIds)
            ->whereDate('next_service_date', '<', $today)
            ->whereNull('service_alert_sent_at')
            ->get();

        if ($newlyOverdue->isEmpty()) {
            $this->info('No newly overdue machines. Nothing to send.');
            return self::SUCCESS;
        }

        $this->info("Found {$newlyOverdue->count()} newly overdue machine(s).");

        // ── Load admin recipients ──────────────────────────────────────────
        $admins = User::whereIn('role', ['super_admin', 'admin'])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        if ($admins->isEmpty()) {
            $this->warn('No admin users found. Aborting.');
            return self::FAILURE;
        }

        // ── Apply mail settings from DB ────────────────────────────────────
        $this->applyMailConfig();

        $settings    = Setting::all()->pluck('value', 'key');
        $companyName = $settings['company_name'] ?? config('app.name');
        $appUrl      = rtrim(config('app.url'), '/');
        $logoPath    = $settings['logo_path'] ?? '';
        $logoUrl     = $logoPath ? $appUrl . '/storage/' . $logoPath : null;

        // ── Send one consolidated email to each admin ──────────────────────
        $overdueList = $newlyOverdue->all();
        $sent        = 0;

        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)
                    ->send(new MachineServiceAlert($overdueList, $companyName, $appUrl, $logoUrl));
                $sent++;
                $this->line("  ✓ Sent to {$admin->email}");
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to send to {$admin->email}: " . $e->getMessage());
            }
        }

        // ── Mark as notified (even if some sends failed, avoid re-flooding) ─
        if ($sent > 0) {
            MachineRuntime::whereIn('id', $newlyOverdue->pluck('id'))
                ->update(['service_alert_sent_at' => now()]);
            $this->info("Marked {$newlyOverdue->count()} record(s) as notified.");
        }

        return self::SUCCESS;
    }

    private function applyMailConfig(): void
    {
        $s = Setting::all()->pluck('value', 'key');

        if (empty($s['mail_host'])) {
            return; // No SMTP configured — use whatever .env has
        }

        config([
            'mail.default'                 => 'smtp',
            'mail.mailers.smtp.host'       => $s['mail_host']         ?? '',
            'mail.mailers.smtp.port'       => (int) ($s['mail_port']  ?? 587),
            'mail.mailers.smtp.username'   => $s['mail_username']     ?? '',
            'mail.mailers.smtp.password'   => $s['mail_password']     ?? '',
            'mail.mailers.smtp.encryption' => $s['mail_encryption']   ?: null,
            'mail.from.address'            => $s['mail_from_address'] ?? config('mail.from.address'),
            'mail.from.name'               => $s['company_name']      ?? config('app.name'),
        ]);
    }
}
