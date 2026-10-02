<x-layouts.admin title="Edit Role">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ $role->name }} — Permissions</h2>
    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 max-w-2xl">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-2 mb-4">
            @foreach($permissions as $permission)
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                        @checked($role->permissions->contains('name', $permission->name)) class="rounded border-gray-300 text-emerald-700">
                    {{ $permission->name }}
                </label>
            @endforeach
        </div>
        <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-semibold px-4 py-2 rounded-md text-sm">Save Permissions</button>
    </form>
</x-layouts.admin>
