<x-layouts.admin title="Categories">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Categories</h2>
        @can('categories.create')
            <a href="{{ route('admin.categories.create') }}" class="bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2 rounded-md">+ New Category</a>
        @endcan
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Parent</th>
                    <th class="px-4 py-2">Active</th>
                    <th class="px-4 py-2">Sort</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $category->name }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $category->parent?->name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $category->is_active ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-2">{{ $category->sort_order }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            @can('categories.update')
                                <a href="{{ route('admin.categories.edit', $category) }}" class="text-emerald-700 hover:underline">Edit</a>
                            @endcan
                            @can('categories.delete')
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $categories->links() }}</div>
</x-layouts.admin>
