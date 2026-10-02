<x-layouts.admin title="Users">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Users</h2>
        @can('users.manage')
            <a href="{{ route('admin.users.create') }}" class="bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2 rounded-md">+ New Staff User</a>
        @endcan
    </div>

    <form method="GET" class="mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name or email..." class="rounded-md border-gray-300 text-sm w-64">
    </form>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">Name</th><th class="px-4 py-2">Email</th><th class="px-4 py-2">Roles</th><th class="px-4 py-2">Active</th><th class="px-4 py-2"></th></tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="px-4 py-2">{{ $user->email }}</td>
                        <td class="px-4 py-2">{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</td>
                        <td class="px-4 py-2">{{ $user->is_active ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-2 text-right">
                            @can('users.manage')
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-emerald-700 hover:underline">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.admin>
