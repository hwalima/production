<?php

namespace App\Console\Commands;

use App\Mail\OverdueActionItemsDigest;
use App\Models\ActionItem;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CheckOverdueActionItems extends Command
{
    protected $signature   = 'action-items:check-overdue';
    protected $description = 'Email managers and admins a daily digest of overdue action items';

    public function handle(): int
    {
        $overdueItems = ActionItem::with('department')
            ->whereNotIn('status', ['completed'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::today())
            ->orderByRaw("FIELD(priority,'high','medium','low')")
            ->get();

        if ($overdueItems->isEmpty()) {
            $this->info('No overdue action items. Nothing to send.');
            return self::SUCCESS;
        }

        $settings    = Setting::all()->pluck('value', 'key');
        $companyName = $settings['company_name'] ?? config('app.name');
        $appUrl      = rtrim(config('app.url'), '/');
        $logoUrl     = $this->resolveLogoUrl($settings);

        if (!empty($settings['mail_host'])) {
            config([
                'mail.default'                 => 'smtp',
                'mail.mailers.smtp.host'       => $settings['mail_host']         ?? '',
                'mail.mailers.smtp.port'       => (int) ($settings['mail_port']  ?? 587),
                'mail.mailers.smtp.username'   => $settings['mail_username']     ?? '',
                'mail.mailers.smtp.password'   => $settings['mail_password']     ?? '',
                'mail.mailers.smtp.encryption' => $settings['mail_encryption']   ?: null,
                'mail.from.address'            => $settings['mail_from_address'] ?? config('mail.from.address'),
                'mail.from.name'               => $companyName,
            ]);
        }

        $recipients = User::whereIn('role', ['super_admin', 'admin', 'manager'])->get();

        foreach ($recipients as $user) {
            Mail::to($user->email)->send(new OverdueActionItemsDigest(
                items:       $overdueItems,
                companyName: $companyName,
                appUrl:      $appUrl,
                logoUrl:     $logoUrl,
            ));
        }

        $count = $overdueItems->count();
        $this->info("Sent overdue digest ({$count} items) to {$recipients->count()} recipient(s).");

        return self::SUCCESS;
    }

    private function resolveLogoUrl(\Illuminate\Support\Collection $settings): ?string
    {
        $logoPath = $settings['logo_path'] ?? '';
        if (!$logoPath) return null;
        $absPath = storage_path('app/public/' . $logoPath);
        if (!file_exists($absPath)) return null;
        $mime = mime_content_type($absPath) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($absPath));
    }
}
