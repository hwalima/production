<?php

namespace App\Http\Controllers;

use App\Mail\AccountDeleted;
use App\Mail\PasswordResetByAdmin;
use App\Mail\RoleChanged;
use App\Mail\WelcomeNewUser;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $actor */
        $actor = auth()->user();
        // Only super_admin may assign the super_admin role
        $allowedRoles = $actor->isSuperAdmin()
            ? ['super_admin', 'admin', 'manager', 'viewer']
            : ['admin', 'manager', 'viewer'];

        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|max:150|unique:users,email',
            'role'      => ['required', Rule::in($allowedRoles)],
            'job_title' => 'nullable|string|max:100',
            'phone'     => 'nullable|string|max:30',
        ]);

        // Auto-generate a cryptographically secure initial password
        $plainPassword = Str::random(10) . chr(random_int(48, 57)) . chr(random_int(65, 90));

        User::create([
            'name'                  => $data['name'],
            'email'                 => $data['email'],
            'role'                  => $data['role'],
            'job_title'             => $data['job_title'] ?? null,
            'phone'                 => $data['phone'] ?? null,
            'password'              => Hash::make($plainPassword),
            'email_verified_at'     => now(),
            'force_password_change' => true,
        ]);

        // ── Send welcome email with login credentials ──────────────────────
        try {
            $settings    = Setting::all()->pluck('value', 'key');
            $companyName = $settings['company_name'] ?? config('app.name');
            $appUrl      = rtrim(config('app.url'), '/');
            $logoUrl     = $this->resolveLogoUrl($settings);

            $this->applyMailSettings($settings);

            Mail::to($data['email'])->send(new WelcomeNewUser(
                userName:      $data['name'],
                userEmail:     $data['email'],
                plainPassword: $plainPassword,
                companyName:   $companyName,
                appUrl:        $appUrl,
                logoUrl:       $logoUrl,
            ));
        } catch (\Exception) {
            // Don't fail user creation if mail delivery fails
        }

        AuditLog::record('user_created', "Created user {$data['name']} ({$data['email']}) with role {$data['role']}", 'User');

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        /** @var \App\Models\User $actor */
        $actor = auth()->user();
        // Only super_admin may assign or keep the super_admin role
        $allowedRoles = $actor->isSuperAdmin()
            ? ['super_admin', 'admin', 'manager', 'viewer']
            : ['admin', 'manager', 'viewer'];

        $rules = [
            'name'      => 'required|string|max:100',
            'email'     => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role'      => ['required', Rule::in($allowedRoles)],
            'job_title' => 'nullable|string|max:100',
            'phone'     => 'nullable|string|max:30',
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['required', Password::min(8)->letters()->numbers()];
        }

        $data = $request->validate($rules);

        $update = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'job_title' => $data['job_title'] ?? null,
            'phone'     => $data['phone'] ?? null,
        ];

        if ($request->filled('password')) {
            $update['password'] = Hash::make($data['password']);
        }

        $oldRole = $user->role;
        $oldName = $user->name;
        $oldEmail = $user->email;
        $user->update($update);

        $changes = [];
        if ($update['name'] !== $oldName) $changes[] = "name";
        if ($update['email'] !== $oldEmail) $changes[] = "email";
        if (($data['role'] ?? $oldRole) !== $oldRole) $changes[] = "role ({$oldRole} → {$data['role']})";
        if ($request->filled('password')) $changes[] = "password";

        // ── Notify user if their role changed ─────────────────────────────
        if (($data['role'] ?? $oldRole) !== $oldRole) {
            try {
                $settings    = Setting::all()->pluck('value', 'key');
                $companyName = $settings['company_name'] ?? config('app.name');
                $appUrl      = rtrim(config('app.url'), '/');
                $logoUrl     = $this->resolveLogoUrl($settings);

                $this->applyMailSettings($settings);

                Mail::to($user->email)->send(new RoleChanged(
                    userName:    $user->name,
                    userEmail:   $user->email,
                    oldRole:     $oldRole,
                    newRole:     $data['role'],
                    companyName: $companyName,
                    appUrl:      $appUrl,
                    logoUrl:     $logoUrl,
                ));
            } catch (\Exception) {
                // Don't fail the update if mail delivery fails
            }
        }

        $changeStr = $changes ? implode(', ', $changes) : 'no field changes';
        AuditLog::record('user_updated', "Updated user {$user->name} ({$user->email}): {$changeStr}", 'User', $user->id);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    // ── Mail helpers ──────────────────────────────────────────────────────

    private function resolveLogoUrl(\Illuminate\Support\Collection $settings): ?string
    {
        $logoPath = $settings['logo_path'] ?? '';
        if (!$logoPath) return null;
        $absPath = storage_path('app/public/' . $logoPath);
        if (!file_exists($absPath)) return null;
        $mime = mime_content_type($absPath) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($absPath));
    }

    private function applyMailSettings(\Illuminate\Support\Collection $settings): void
    {
        if (empty($settings['mail_host'])) return;
        $companyName = $settings['company_name'] ?? config('app.name');
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

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account here.');
        }

        /** @var \App\Models\User $actor */
        $actor = auth()->user();
        if ($user->isSuperAdmin() && !$actor->isSuperAdmin()) {
            return redirect()->route('users.index')->with('error', 'Only a Super Administrator can delete another Super Administrator.');
        }

        // ── Notify the user their account has been removed ────────────────
        try {
            $settings    = Setting::all()->pluck('value', 'key');
            $companyName = $settings['company_name'] ?? config('app.name');
            $appUrl      = rtrim(config('app.url'), '/');
            $logoUrl     = $this->resolveLogoUrl($settings);

            $this->applyMailSettings($settings);

            Mail::to($user->email)->send(new AccountDeleted(
                userName:    $user->name,
                userEmail:   $user->email,
                companyName: $companyName,
                appUrl:      $appUrl,
                logoUrl:     $logoUrl,
            ));
        } catch (\Exception) {
            // Don't fail the deletion if mail delivery fails
        }

        $deletedName  = $user->name;
        $deletedEmail = $user->email;
        $user->delete();

        AuditLog::record('user_deleted', "Deleted user {$deletedName} ({$deletedEmail})", 'User');

        return redirect()->route('users.index')->with('success', 'User deleted.');
    }

    public function resetPassword(User $user)
    {
        /** @var \App\Models\User $actor */
        $actor = auth()->user();

        if (!$actor->isSuperAdmin()) {
            return back()->with('error', 'Only a Super Administrator can reset passwords.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Use your profile page to change your own password.');
        }

        // Generate a strong random temporary password
        $plainPassword = ucfirst(Str::random(6)) . rand(10, 99) . '!';

        $user->update([
            'password'              => Hash::make($plainPassword),
            'force_password_change' => true,
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ]);

        // Email the new temporary password to the user
        try {
            $settings    = Setting::all()->pluck('value', 'key');
            $companyName = $settings['company_name'] ?? config('app.name');
            $appUrl      = rtrim(config('app.url'), '/');
            $logoUrl     = $this->resolveLogoUrl($settings);

            $this->applyMailSettings($settings);

            Mail::to($user->email)->send(new PasswordResetByAdmin(
                userName:      $user->name,
                userEmail:     $user->email,
                plainPassword: $plainPassword,
                resetBy:       $actor->name,
                companyName:   $companyName,
                appUrl:        $appUrl,
                logoUrl:       $logoUrl,
            ));

            $mailNote = ' A temporary password has been emailed to them.';
        } catch (\Exception) {
            $mailNote = ' (Email delivery failed — temporary password: ' . $plainPassword . ')';
        }

        AuditLog::record('user_password_reset', "Super admin {$actor->name} reset password for {$user->name} ({$user->email})", 'User', $user->id);

        return back()->with('success', "Password reset for {$user->name}.{$mailNote}");
    }

    public function toggleActive(User $user)    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        /** @var \App\Models\User $actor */
        $actor = auth()->user();
        if ($user->isSuperAdmin() && !$actor->isSuperAdmin()) {
            return back()->with('error', 'Only a Super Administrator can change a Super Administrator\'s status.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        AuditLog::record('user_status_changed', "User {$user->name} ({$user->email}) {$status}", 'User', $user->id);
        return back()->with('success', "User {$user->name} has been {$status}.");
    }
}
