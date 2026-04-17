<?php

namespace App\Console\Commands;

use App\Mail\ConsumableLowStockAlert;
use App\Models\Consumable;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckConsumableLowStock extends Command
{
    protected $signature   = 'consumables:check-low-stock';
    protected $description = 'Email all users when any consumable item is below its reorder level';

    public function handle(): int
    {
        // ── Load all active consumables that have a reorder threshold set ──
        $consumables = Consumable::where('is_active', true)
            ->where('reorder_level', '>', 0)
            ->with([
                'movements' => fn ($q) => $q->select('consumable_id', 'direction', 'quantity'),
            ])
            ->get();

        if ($consumables->isEmpty()) {
            $this->info('No consumables with reorder levels configured. Nothing to check.');
            return self::SUCCESS;
        }

        // ── Identify items that are at or below reorder level ──────────────
        $lowItems = $consumables
            ->map(function ($c) {
                $in      = $c->movements->where('direction', 'in')->sum('quantity');
                $out     = $c->movements->where('direction', 'out')->sum('quantity');
                $current = (float) $in - (float) $out;
                $reorder = (float) $c->reorder_level;

                if ($current > $reorder) {
                    return null;
                }

                return [
                    'name'          => $c->name,
                    'category'      => $c->category,
                    'use_unit'      => $c->use_unit,
                    'current_stock' => $current,
                    'reorder_level' => $reorder,
                    'deficit'       => max(0, $reorder - $current),
                ];
            })
            ->filter()
            ->sortBy('name')
            ->values()
            ->all();

        if (empty($lowItems)) {
            $this->info('All consumables are above their reorder levels. No alert needed.');
            return self::SUCCESS;
        }

        $this->info('Found ' . count($lowItems) . ' item(s) at or below reorder level.');

        // ── Load all users ─────────────────────────────────────────────────
        $users = User::whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        if ($users->isEmpty()) {
            $this->warn('No users found. Aborting.');
            return self::FAILURE;
        }

        // ── Apply mail settings from DB ────────────────────────────────────
        $this->applyMailConfig();

        $settings    = Setting::all()->pluck('value', 'key');
        $companyName = $settings['company_name'] ?? config('app.name');
        $appUrl      = rtrim(config('app.url'), '/');

        // ── Send consolidated email to every user ──────────────────────────
        $sent = 0;

        foreach ($users as $user) {
            try {
                Mail::to($user->email)
                    ->send(new ConsumableLowStockAlert($lowItems, $companyName, $appUrl));
                $sent++;
                $this->line("  ✓ Sent to {$user->email}");
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to send to {$user->email}: " . $e->getMessage());
            }
        }

        $this->info("Alert sent to {$sent} user(s).");

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
