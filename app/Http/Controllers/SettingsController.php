<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name'     => 'required|string|max:200',
            'company_location' => 'nullable|string|max:200',
            'company_address'  => 'nullable|string|max:255',
            'company_phone'    => 'nullable|string|max:50',
            'company_email'    => 'nullable|email|max:100',
            'company_website'  => 'nullable|url|max:200',
            'zesa_daily'       => 'required|numeric|min:0',
            'diesel_daily'     => 'required|numeric|min:0',
            'labour_daily'     => 'required|numeric|min:0',
            'logo'             => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            // email / SMTP
            'mail_host'        => 'nullable|string|max:255',
            'mail_port'        => 'nullable|integer|min:1|max:65535',
            'mail_username'    => 'nullable|string|max:255',
            'mail_password'    => 'nullable|string|max:255',
            'mail_encryption'  => 'nullable|in:tls,ssl,starttls,',
            'mail_from_address'=> 'nullable|email|max:255',
            'mail_from_name'   => 'nullable|string|max:255',
            // currency
            'currency_symbol'  => 'nullable|string|max:10',
            'currency_code'    => 'nullable|string|max:10',
        ]);

        $scalar = [
            'company_name', 'company_location', 'company_address',
            'company_phone', 'company_email', 'company_website',
            'zesa_daily', 'diesel_daily', 'labour_daily',
            'mail_host', 'mail_port', 'mail_username',
            'mail_encryption', 'mail_from_address', 'mail_from_name',
            'currency_symbol', 'currency_code',
        ];

        foreach ($scalar as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => $request->input($key) ?? '']);
        }

        // Only overwrite password if a new one was provided
        if ($request->filled('mail_password')) {
            Setting::updateOrCreate(['key' => 'mail_password'], ['value' => $request->input('mail_password')]);
        } elseif (!Setting::where('key', 'mail_password')->exists()) {
            Setting::create(['key' => 'mail_password', 'value' => '']);
        }

        // Handle logo upload
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $old = Setting::where('key', 'logo_path')->value('value');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }

            $ext      = $request->file('logo')->getClientOriginalExtension();
            $filename = 'logo.' . $ext;
            $path     = $request->file('logo')->storeAs('brand', $filename, 'public');

            $logoAbsPath = storage_path('app/public/' . $path);
            copy($logoAbsPath, public_path('favicon.ico'));

            Setting::updateOrCreate(['key' => 'logo_path'], ['value' => $path]);
        }

        return redirect()->route('settings.index')->with('success', 'Settings saved successfully.');
    }

    public function testEmail(Request $request)
    {
        $request->validate(['test_email' => 'required|email']);

        $settings = Setting::all()->pluck('value', 'key');

        // Temporarily override the mailer config with DB values
        config([
            'mail.default'                        => 'smtp',
            'mail.mailers.smtp.host'              => $settings['mail_host']         ?? '',
            'mail.mailers.smtp.port'              => (int) ($settings['mail_port']  ?? 587),
            'mail.mailers.smtp.username'          => $settings['mail_username']     ?? '',
            'mail.mailers.smtp.password'          => $settings['mail_password']     ?? '',
            'mail.mailers.smtp.encryption'        => $settings['mail_encryption']   ?: null,
            'mail.from.address'                   => $settings['mail_from_address'] ?? config('mail.from.address'),
            'mail.from.name'                      => $settings['mail_from_name']    ?? config('app.name'),
        ]);

        try {
            Mail::raw(
                'This is a test email from ' . ($settings['company_name'] ?? config('app.name')) . '. Your email settings are working correctly.',
                function ($message) use ($request, $settings) {
                    $message->to($request->test_email)
                            ->subject('Test Email — ' . ($settings['company_name'] ?? config('app.name')));
                }
            );
            return back()->with('email_success', 'Test email sent to ' . $request->test_email . '. Check your inbox.');
        } catch (\Exception $e) {
            return back()->with('email_error', $e->getMessage());
        }
    }
}
