<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('users.view');

        $users = User::query()
            ->with('roles')
            ->when($request->query('q'), fn ($q, $term) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $this->authorize('users.manage');

        $roles = Role::query()->where('name', '!=', 'Customer')->orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->authorize('users.manage');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $user->syncRoles($data['roles']);

        return redirect()->route('admin.users.index')->with('status', 'Staff user created.');
    }

    public function edit(User $user)
    {
        $this->authorize('users.manage');

        $roles = Role::query()->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('users.manage');

        $data = $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $user->syncRoles($data['roles'] ?? []);
        $user->update(['is_active' => $data['is_active'] ?? false]);

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    public function destroy(User $user)
    {
        $this->authorize('users.manage');

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'User deactivated.');
    }
}
