<x-layouts.admin title="Purchase Orders">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Purchase Orders</h2>
        @can('purchases.create')
            <a href="{{ route('admin.purchase-orders.create') }}" class="bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2 rounded-md">+ New Purchase Order</a>
        @endcan
    </div>
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">PO #</th><th class="px-4 py-2">Supplier</th><th class="px-4 py-2">Warehouse</th><th class="px-4 py-2">Total</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th></tr>
            </thead>
            <tbody>
                @foreach($purchaseOrders as $po)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $po->po_number }}</td>
                        <td class="px-4 py-2">{{ $po->supplier->name }}</td>
                        <td class="px-4 py-2">{{ $po->warehouse->name }}</td>
                        <td class="px-4 py-2">₹{{ number_format((float) $po->total_amount, 2) }}</td>
                        <td class="px-4 py-2">{{ ucfirst(str_replace('_', ' ', $po->status->value)) }}</td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('admin.purchase-orders.show', $po) }}" class="text-emerald-700 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $purchaseOrders->links() }}</div>
</x-layouts.admin>
