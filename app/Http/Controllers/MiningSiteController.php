<?php

namespace App\Http\Controllers;

use App\Models\MiningSite;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class MiningSiteController extends Controller
{
    public function index()
    {
        $sites = MiningSite::orderBy('name')->get();
        return view('settings.mining-sites', compact('sites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:mining_sites,name',
            'description' => 'nullable|string|max:255',
        ]);

        MiningSite::create([
            'name'        => trim($request->name),
            'description' => trim($request->description ?? ''),
            'is_active'   => true,
        ]);

        AuditLog::record('mining_site_created', "Added mining site: {$request->name}", 'MiningSite');

        return redirect()->route('mining-sites.index')->with('success', 'Mining site added.');
    }

    public function edit(MiningSite $miningSite)
    {
        $sites = MiningSite::orderBy('name')->get();
        return view('settings.mining-sites', ['sites' => $sites, 'editing' => $miningSite]);
    }

    public function update(Request $request, MiningSite $miningSite)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:mining_sites,name,' . $miningSite->id,
            'description' => 'nullable|string|max:255',
        ]);

        $miningSite->update([
            'name'        => trim($request->name),
            'description' => trim($request->description ?? ''),
            'is_active'   => $request->boolean('is_active', true),
        ]);

        AuditLog::record('mining_site_updated', "Updated mining site: {$miningSite->name}", 'MiningSite', $miningSite->id);

        return redirect()->route('mining-sites.index')->with('success', 'Mining site updated.');
    }

    public function destroy(MiningSite $miningSite)
    {
        $siteId   = $miningSite->id;
        $siteName = $miningSite->name;
        $miningSite->delete();
        AuditLog::record('mining_site_deleted', "Deleted mining site: {$siteName}", 'MiningSite', $siteId);
        return redirect()->route('mining-sites.index')->with('success', 'Mining site deleted.');
    }

    /** Toggle active status via PATCH */
    public function toggle(MiningSite $miningSite)
    {
        $miningSite->update(['is_active' => !$miningSite->is_active]);
        $status = $miningSite->is_active ? 'activated' : 'deactivated';
        AuditLog::record('mining_site_toggled', "Mining site {$miningSite->name} {$status}", 'MiningSite', $miningSite->id);
        return redirect()->route('mining-sites.index');
    }
}
