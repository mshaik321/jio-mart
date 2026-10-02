<x-layouts.admin title="Brands">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Brands</h2>
        @can('brands.create')
            <a href="{{ route('admin.brands.create') }}" class="bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2 rounded-md">+ New Brand</a>
        @endcan
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">Name</th><th class="px-4 py-2">Active</th><th class="px-4 py-2"></th></tr>
            </thead>
            <tbody>
                @foreach($brands as $brand)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $brand->name }}</td>
                        <td class="px-4 py-2">{{ $brand->is_active ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            @can('brands.update')
                                <a href="{{ route('admin.brands.edit', $brand) }}" class="text-emerald-700 hover:underline">Edit</a>
                            @endcan
                            @can('brands.delete')
                                <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="inline" onsubmit="return confirm('Delete this brand?');">
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
    <div class="mt-4">{{ $brands->links() }}</div>
</x-layouts.admin>
