<x-layouts.admin title="New Purchase Order">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">New Purchase Order</h2>

    <form action="{{ route('admin.purchase-orders.store') }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 space-y-4 max-w-4xl" x-data="{ rows: [0] }">
        @csrf
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                <select name="supplier_id" class="w-full rounded-md border-gray-300 text-sm" required>
                    <option value="">Select</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                <select name="warehouse_id" class="w-full rounded-md border-gray-300 text-sm" required>
                    <option value="">Select</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Order Date</label>
                <input type="date" name="order_date" value="{{ now()->format('Y-m-d') }}" class="w-full rounded-md border-gray-300 text-sm" required>
            </div>
        </div>

        <div>
            <p class="text-sm font-semibold text-gray-900 mb-2">Items</p>
            <template x-for="(row, index) in rows" :key="index">
                <div class="grid grid-cols-4 gap-3 mb-2">
                    <select :name="`items[${index}][product_variant_id]`" class="rounded-md border-gray-300 text-sm" required>
                        <option value="">Select variant</option>
                        @foreach($variants as $variant)
                            <option value="{{ $variant->id }}">{{ $variant->sku }} — {{ $variant->product->name }} ({{ $variant->variant_name }})</option>
                        @endforeach
                    </select>
                    <input type="number" :name="`items[${index}][quantity_ordered]`" placeholder="Quantity" class="rounded-md border-gray-300 text-sm" required>
                    <input type="number" step="0.01" :name="`items[${index}][unit_cost]`" placeholder="Unit cost" class="rounded-md border-gray-300 text-sm" required>
                </div>
            </template>
            <button type="button" @click="rows.push(rows.length)" class="text-sm text-brand-700 underline">+ Add another item</button>
        </div>

        <button type="submit" class="bg-fresh-600 hover:bg-fresh-700 text-white font-semibold px-4 py-2 rounded-md text-sm">Create Purchase Order</button>
    </form>
</x-layouts.admin>
