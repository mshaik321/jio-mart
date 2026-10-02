<x-layouts.admin title="Edit Product">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Edit Product — {{ $product->name }}</h2>

    <form action="{{ route('admin.products.update', $product) }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 space-y-4 max-w-3xl mb-8">
        @csrf @method('PUT')
        @include('admin.products._form')
        <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-semibold px-4 py-2 rounded-md text-sm">Update Product</button>
    </form>

    <div class="max-w-3xl">
        <h3 class="font-semibold text-gray-900 mb-3">Variants</h3>

        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-4">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-500">
                    <tr><th class="px-4 py-2">SKU</th><th class="px-4 py-2">Name</th><th class="px-4 py-2">Price</th><th class="px-4 py-2">Stock</th><th class="px-4 py-2">Default</th><th class="px-4 py-2"></th></tr>
                </thead>
                <tbody>
                    @foreach($product->variants as $variant)
                        <tr class="border-t border-gray-100">
                            <td class="px-4 py-2 text-gray-500">{{ $variant->sku }}</td>
                            <td class="px-4 py-2 font-medium text-gray-900">{{ $variant->variant_name }}</td>
                            <td class="px-4 py-2">₹{{ number_format((float) $variant->selling_price, 2) }}</td>
                            <td class="px-4 py-2">{{ $variant->totalStock() }}</td>
                            <td class="px-4 py-2">{{ $variant->is_default ? 'Yes' : '' }}</td>
                            <td class="px-4 py-2 text-right">
                                <form action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}" method="POST" class="inline" onsubmit="return confirm('Remove this variant?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <p class="font-medium text-gray-900 mb-3">Add Variant</p>
            <form action="{{ route('admin.products.variants.store', $product) }}" method="POST" class="grid grid-cols-6 gap-3 items-end">
                @csrf
                <div class="col-span-1">
                    <label class="block text-xs text-gray-600 mb-1">SKU</label>
                    <input type="text" name="sku" class="w-full rounded-md border-gray-300 text-sm" required>
                </div>
                <div class="col-span-1">
                    <label class="block text-xs text-gray-600 mb-1">Variant Name</label>
                    <input type="text" name="variant_name" placeholder="1 KG" class="w-full rounded-md border-gray-300 text-sm" required>
                </div>
                <div class="col-span-1">
                    <label class="block text-xs text-gray-600 mb-1">MRP</label>
                    <input type="number" step="0.01" name="mrp" class="w-full rounded-md border-gray-300 text-sm" required>
                </div>
                <div class="col-span-1">
                    <label class="block text-xs text-gray-600 mb-1">Selling Price</label>
                    <input type="number" step="0.01" name="selling_price" class="w-full rounded-md border-gray-300 text-sm" required>
                </div>
                <div class="col-span-1">
                    <label class="block text-xs text-gray-600 mb-1">Initial Stock</label>
                    <input type="number" name="initial_quantity" value="0" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                <div class="col-span-1 flex items-center gap-2">
                    <label class="flex items-center gap-1 text-xs text-gray-700">
                        <input type="checkbox" name="is_default" value="1" class="rounded border-gray-300 text-emerald-700"> Default
                    </label>
                </div>
                <div class="col-span-6">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold px-4 py-2 rounded-md">Add Variant</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
