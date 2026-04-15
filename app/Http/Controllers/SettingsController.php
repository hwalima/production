<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        ]);

        $scalar = [
            'company_name', 'company_location', 'company_address',
            'company_phone', 'company_email', 'company_website',
            'zesa_daily', 'diesel_daily', 'labour_daily',
        ];

        foreach ($scalar as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => $request->input($key) ?? '']);
        }

        // Handle logo upload
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            // Delete old logo if exists
            $old = Setting::where('key', 'logo_path')->value('value');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }

            $ext      = $request->file('logo')->getClientOriginalExtension();
            $filename = 'logo.' . $ext;
            $path     = $request->file('logo')->storeAs('brand', $filename, 'public');

            // Also copy as favicon.ico to public root
            $logoAbsPath = storage_path('app/public/' . $path);
            copy($logoAbsPath, public_path('favicon.ico'));

            Setting::updateOrCreate(['key' => 'logo_path'], ['value' => $path]);
        }

        return redirect()->route('settings.index')->with('success', 'Settings saved successfully.');
    }
}
