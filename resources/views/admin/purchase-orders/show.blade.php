<x-layouts.admin :title="$purchaseOrder->po_number">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $purchaseOrder->po_number }}</h2>
            <p class="text-sm text-gray-500">{{ $purchaseOrder->supplier->name }} &middot; {{ $purchaseOrder->warehouse->name }}</p>
        </div>
        <span class="text-sm px-3 py-1 rounded-full bg-gray-100 text-gray-700 font-medium">{{ ucfirst(str_replace('_', ' ', $purchaseOrder->status->value)) }}</span>
    </div>

    <form action="{{ route('admin.purchase-orders.receive', $purchaseOrder) }}" method="POST" class="bg-white border border-gray-200 rounded-lg overflow-x-auto">
        @csrf
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">SKU</th><th class="px-4 py-2">Product</th><th class="px-4 py-2">Ordered</th><th class="px-4 py-2">Received</th><th class="px-4 py-2">Unit Cost</th><th class="px-4 py-2">Receive Now</th></tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $item)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 text-gray-500">{{ $item->variant->sku }}</td>
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $item->variant->product->name }} ({{ $item->variant->variant_name }})</td>
                        <td class="px-4 py-2">{{ $item->quantity_ordered }}</td>
                        <td class="px-4 py-2">{{ $item->quantity_received }}</td>
                        <td class="px-4 py-2">₹{{ number_format((float) $item->unit_cost, 2) }}</td>
                        <td class="px-4 py-2">
                            @if($item->remainingQuantity() > 0)
                                <input type="number" name="receive[{{ $item->id }}]" min="0" max="{{ $item->remainingQuantity() }}" placeholder="0" class="w-24 rounded-md border-gray-300 text-sm">
                            @else
                                <span class="text-emerald-700 text-xs">Fully received</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">
            @can('purchases.update')
                <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-semibold px-4 py-2 rounded-md text-sm">Record Receipt</button>
            @endcan
        </div>
    </form>
</x-layouts.admin>
