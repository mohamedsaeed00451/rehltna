<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    private array $permissions_list = [
        'manage_blogs' => 'Blogs System',
        'manage_trips' => 'Trips System',
        'manage_locations' => 'Location System',
        'manage_customers' => 'Customers',
        'manage_notifications' => 'Notifications',
        'manage_payments' => 'Payments',
        'manage_website' => 'Website Content',
        'manage_settings' => 'Settings',
        'manage_staff' => 'Staff & Roles',
    ];

    public function index(): View
    {
        $roles = Role::query()->withCount('users')->orderByDesc('id')->paginate(10);
        return view('pages.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissions = $this->permissions_list;
        return view('pages.roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Role::class, 'name')
            ],
            'permissions' => 'nullable|array',
        ]);

        Role::query()->create([
            'name' => $request->name,
            'permissions' => $request->permissions ?? [],
        ]);

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit($id): View|RedirectResponse
    {
        $role = Role::query()->findOrFail(decrypt($id));
        if (strtolower($role->name) === 'admin') {
            return redirect()->route('roles.index')->with('error', 'The Admin role is a system role and cannot be edited.');
        }
        $permissions = $this->permissions_list;
        return view('pages.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $role = Role::query()->findOrFail($id);
        if (strtolower($role->name) === 'admin') {
            return redirect()->route('roles.index')->with('error', 'The Admin role is a system role and cannot be edited.');
        }
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Role::class, 'name')->ignore($role->id)
            ],
            'permissions' => 'nullable|array',
        ]);

        $role->update([
            'name' => $request->name,
            'permissions' => $request->permissions ?? [],
        ]);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $role = Role::query()->findOrFail($id);
        if (strtolower($role->name) === 'admin') {
            return redirect()->route('roles.index')->with('error', 'The Admin role is a system role and cannot be deleted.');
        }
        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete role assigned to users.');
        }
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
