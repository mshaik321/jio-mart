<x-layouts.admin title="Products">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Products</h2>
        @can('products.create')
            <a href="{{ route('admin.products.create') }}" class="bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2 rounded-md">+ New Product</a>
        @endcan
    </div>

    <form method="GET" class="mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products..." class="rounded-md border-gray-300 text-sm w-64">
    </form>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">SKU</th><th class="px-4 py-2">Name</th><th class="px-4 py-2">Category</th><th class="px-4 py-2">Price</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th></tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 text-gray-500">{{ $product->sku }}</td>
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $product->name }}</td>
                        <td class="px-4 py-2">{{ $product->category->name }}</td>
                        <td class="px-4 py-2">₹{{ number_format((float) $product->selling_price, 2) }}</td>
                        <td class="px-4 py-2">{{ ucfirst($product->status->value) }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            @can('products.update')
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-emerald-700 hover:underline">Edit</a>
                            @endcan
                            @can('products.delete')
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('Delete this product?');">
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
    <div class="mt-4">{{ $products->links() }}</div>
</x-layouts.admin>
