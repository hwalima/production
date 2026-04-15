<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    /** Canonical role definition used by both index and assign. */
    public static function roleDefinitions(): array
    {
        return [
            'super_admin' => [
                'label'       => 'Super Administrator',
                'description' => 'Unrestricted access — manages all roles, users, settings, and every operational module. Cannot be modified by Administrators.',
                'badge_color' => '#7c3aed',
                'permissions' => [
                    'View all modules'                    => true,
                    'Create / edit / delete records'      => true,
                    'Access production reports'           => true,
                    'Manage shifts & mining sites'        => true,
                    'Manage company settings'             => true,
                    'Manage users'                        => true,
                    'Manage roles & assign roles'         => true,
                ],
            ],
            'admin' => [
                'label'       => 'Administrator',
                'description' => 'Full operational access — manages users, settings, and all modules. Cannot manage roles.',
                'badge_color' => '#dc2626',
                'permissions' => [
                    'View all modules'                    => true,
                    'Create / edit / delete records'      => true,
                    'Access production reports'           => true,
                    'Manage shifts & mining sites'        => true,
                    'Manage company settings'             => true,
                    'Manage users'                        => true,
                    'Manage roles & assign roles'         => false,
                ],
            ],
            'manager' => [
                'label'       => 'Manager',
                'description' => 'Write access to operational modules; cannot access settings or user management.',
                'badge_color' => '#d97706',
                'permissions' => [
                    'View all modules'                    => true,
                    'Create / edit / delete records'      => true,
                    'Access production reports'           => true,
                    'Manage shifts & mining sites'        => false,
                    'Manage company settings'             => false,
                    'Manage users'                        => false,
                    'Manage roles & assign roles'         => false,
                ],
            ],
            'viewer' => [
                'label'       => 'Viewer',
                'description' => 'Read-only — can view all records and reports but cannot create, edit, or delete.',
                'badge_color' => '#6b7280',
                'permissions' => [
                    'View all modules'                    => true,
                    'Create / edit / delete records'      => false,
                    'Access production reports'           => true,
                    'Manage shifts & mining sites'        => false,
                    'Manage company settings'             => false,
                    'Manage users'                        => false,
                    'Manage roles & assign roles'         => false,
                ],
            ],
        ];
    }

    public function index(): View
    {
        $roles = self::roleDefinitions();

        // Attach live user counts
        $counts = User::selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->toArray();

        foreach ($roles as $key => &$data) {
            $data['users_count'] = $counts[$key] ?? 0;
        }
        unset($data);

        // Users list keyed by role (for detail rows + role-edit dropdowns)
        $usersByRole = User::orderBy('name')->get()->groupBy('role');
        $allUsers    = User::orderBy('name')->get();

        return view('roles.index', compact('roles', 'usersByRole', 'allUsers'));
    }

    /**
     * PATCH /roles/{user}/assign
     * Super admin changes a single user's role.
     */
    public function assign(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(array_keys(self::roleDefinitions()))],
        ]);

        // Prevent self-demotion
        if ($user->id === auth()->id() && $data['role'] !== 'super_admin') {
            return back()->with('error', 'You cannot change your own role.');
        }

        $user->update(['role' => $data['role']]);

        return redirect()->route('roles.index')
            ->with('success', "{$user->name}'s role updated to " . self::roleDefinitions()[$data['role']]['label'] . '.');
    }
}
