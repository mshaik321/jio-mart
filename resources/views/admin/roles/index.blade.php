<x-layouts.admin title="Roles & Permissions">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Roles &amp; Permissions</h2>
    <div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">Role</th><th class="px-4 py-2">Permissions</th><th class="px-4 py-2"></th></tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $role->name }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $role->permissions->count() }} permission(s)</td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('admin.roles.edit', $role) }}" class="text-emerald-700 hover:underline">Edit Permissions</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.admin>
