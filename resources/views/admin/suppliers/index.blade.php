<x-layouts.admin title="Suppliers">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Suppliers</h2>
        @can('suppliers.create')
            <a href="{{ route('admin.suppliers.create') }}" class="bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2 rounded-md">+ New Supplier</a>
        @endcan
    </div>
    <div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">Name</th><th class="px-4 py-2">Contact</th><th class="px-4 py-2">Phone</th><th class="px-4 py-2">Active</th><th class="px-4 py-2"></th></tr>
            </thead>
            <tbody>
                @foreach($suppliers as $supplier)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $supplier->name }}</td>
                        <td class="px-4 py-2">{{ $supplier->contact_person }}</td>
                        <td class="px-4 py-2">{{ $supplier->phone }}</td>
                        <td class="px-4 py-2">{{ $supplier->is_active ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            @can('suppliers.update')
                                <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="text-emerald-700 hover:underline">Edit</a>
                            @endcan
                            @can('suppliers.delete')
                                <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST" class="inline" onsubmit="return confirm('Delete this supplier?');">
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
    <div class="mt-4">{{ $suppliers->links() }}</div>
</x-layouts.admin>
