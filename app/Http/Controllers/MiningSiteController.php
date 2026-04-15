<?php

namespace App\Http\Controllers;

use App\Models\MiningSite;
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

        return redirect()->route('mining-sites.index')->with('success', 'Mining site updated.');
    }

    public function destroy(MiningSite $miningSite)
    {
        $miningSite->delete();
        return redirect()->route('mining-sites.index')->with('success', 'Mining site deleted.');
    }

    /** Toggle active status via PATCH */
    public function toggle(MiningSite $miningSite)
    {
        $miningSite->update(['is_active' => !$miningSite->is_active]);
        return redirect()->route('mining-sites.index');
    }
}
