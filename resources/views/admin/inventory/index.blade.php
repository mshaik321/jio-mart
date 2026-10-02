<x-layouts.admin title="Inventory">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Inventory</h2>

    <form method="GET" class="mb-4 flex gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products..." class="rounded-md border-gray-300 text-sm w-64">
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="low_stock" value="1" @checked(request('low_stock')) onchange="this.form.submit()" class="rounded border-gray-300 text-emerald-700">
            Low stock only
        </label>
    </form>

    @can('inventory.adjust')
        <div class="bg-white border border-gray-200 rounded-lg p-4 mb-6 max-w-2xl">
            <p class="font-medium text-gray-900 mb-2 text-sm">Manual Stock Adjustment</p>
            <form action="{{ route('admin.inventory.adjust') }}" method="POST" class="grid grid-cols-5 gap-3 items-end">
                @csrf
                <div class="col-span-1">
                    <label class="block text-xs text-gray-600 mb-1">Warehouse ID</label>
                    <input type="number" name="warehouse_id" class="w-full rounded-md border-gray-300 text-sm" required>
                </div>
                <div class="col-span-1">
                    <label class="block text-xs text-gray-600 mb-1">Variant ID</label>
                    <input type="number" name="product_variant_id" class="w-full rounded-md border-gray-300 text-sm" required>
                </div>
                <div class="col-span-1">
                    <label class="block text-xs text-gray-600 mb-1">Qty (+/-)</label>
                    <input type="number" name="quantity" class="w-full rounded-md border-gray-300 text-sm" required>
                </div>
                <div class="col-span-1">
                    <label class="block text-xs text-gray-600 mb-1">Note</label>
                    <input type="text" name="note" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                <div class="col-span-1">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold px-4 py-2 rounded-md w-full">Adjust</button>
                </div>
            </form>
        </div>
    @endcan

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">Variant ID</th><th class="px-4 py-2">Product</th><th class="px-4 py-2">Warehouse</th><th class="px-4 py-2">On Hand</th><th class="px-4 py-2">Reserved</th><th class="px-4 py-2">Available</th><th class="px-4 py-2"></th></tr>
            </thead>
            <tbody>
                @foreach($inventories as $inventory)
                    <tr class="border-t border-gray-100 {{ $inventory->isLowStock() ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-2 text-gray-500">{{ $inventory->product_variant_id }}</td>
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $inventory->variant->product->name }} ({{ $inventory->variant->variant_name }})</td>
                        <td class="px-4 py-2">{{ $inventory->warehouse->name }}</td>
                        <td class="px-4 py-2">{{ $inventory->quantity_on_hand }}</td>
                        <td class="px-4 py-2">{{ $inventory->quantity_reserved }}</td>
                        <td class="px-4 py-2 font-semibold {{ $inventory->isLowStock() ? 'text-red-600' : 'text-gray-900' }}">{{ $inventory->availableQuantity() }}</td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('admin.inventory.movements', $inventory->variant) }}" class="text-emerald-700 hover:underline">History</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $inventories->links() }}</div>
</x-layouts.admin>
