<x-layouts.admin title="New Staff User">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">New Staff User</h2>
    <form action="{{ route('admin.users.store') }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 space-y-4 max-w-lg">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-md border-gray-300 text-sm" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-md border-gray-300 text-sm" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" class="w-full rounded-md border-gray-300 text-sm" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Roles</label>
            @foreach($roles as $role)
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="rounded border-gray-300 text-brand-700">
                    {{ $role->name }}
                </label>
            @endforeach
        </div>
        <button type="submit" class="bg-fresh-600 hover:bg-fresh-700 text-white font-semibold px-4 py-2 rounded-md text-sm">Create User</button>
    </form>
</x-layouts.admin>
