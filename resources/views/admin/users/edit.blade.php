<x-layouts.admin title="Edit User">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Edit User — {{ $user->name }}</h2>
    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 space-y-4 max-w-lg">
        @csrf @method('PUT')
        <p class="text-sm text-gray-500">{{ $user->email }}</p>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Roles</label>
            @foreach($roles as $role)
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked($user->hasRole($role->name)) class="rounded border-gray-300 text-brand-700">
                    {{ $role->name }}
                </label>
            @endforeach
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" @checked($user->is_active) class="rounded border-gray-300 text-brand-700">
            Active
        </label>

        <button type="submit" class="bg-fresh-600 hover:bg-fresh-700 text-white font-semibold px-4 py-2 rounded-md text-sm">Update User</button>
    </form>
</x-layouts.admin>
