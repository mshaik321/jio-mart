<x-layouts.admin title="Reports">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Reports</h2>

    <form method="GET" class="mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-600 mb-1">From</label>
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="rounded-md border-gray-300 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">To</label>
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="rounded-md border-gray-300 text-sm">
        </div>
        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold px-4 py-2 rounded-md">Apply</button>
    </form>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Revenue</p>
            <p class="text-2xl font-bold text-gray-900">₹{{ number_format($totalRevenue, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Orders</p>
            <p class="text-2xl font-bold text-gray-900">{{ $totalOrders }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Discounts Given</p>
            <p class="text-2xl font-bold text-gray-900">₹{{ number_format($totalDiscount, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Tax Collected</p>
            <p class="text-2xl font-bold text-gray-900">₹{{ number_format($totalTax, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Sales by Day</h3>
            <table class="w-full text-sm">
                <thead class="text-left text-gray-500"><tr><th class="pb-2">Date</th><th class="pb-2">Orders</th><th class="pb-2">Revenue</th></tr></thead>
                <tbody>
                    @foreach($salesByDay as $row)
                        <tr class="border-t border-gray-100">
                            <td class="py-1.5">{{ $row->day }}</td>
                            <td class="py-1.5">{{ $row->orders }}</td>
                            <td class="py-1.5">₹{{ number_format((float) $row->revenue, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Orders by Status</h3>
            <table class="w-full text-sm">
                <tbody>
                    @foreach($statusBreakdown as $status => $count)
                        <tr class="border-t border-gray-100 first:border-0">
                            <td class="py-1.5 capitalize">{{ str_replace('_', ' ', $status) }}</td>
                            <td class="py-1.5 text-right font-semibold">{{ $count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Top Products</h3>
            <table class="w-full text-sm">
                <thead class="text-left text-gray-500"><tr><th class="pb-2">Product</th><th class="pb-2">Units Sold</th><th class="pb-2">Revenue</th></tr></thead>
                <tbody>
                    @foreach($topProducts as $product)
                        <tr class="border-t border-gray-100">
                            <td class="py-1.5">{{ $product->product_name }}</td>
                            <td class="py-1.5">{{ $product->units_sold }}</td>
                            <td class="py-1.5">₹{{ number_format((float) $product->revenue, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-5">
            <h3 class="font-semibold text-gray-900 mb-3">Inventory</h3>
            <p class="text-sm text-gray-700">Low stock items: <span class="font-bold text-red-600">{{ $lowStockCount }}</span></p>
            <a href="{{ route('admin.inventory.index', ['low_stock' => 1]) }}" class="text-sm text-brand-700 underline">View low stock items</a>
        </div>
    </div>
</x-layouts.admin>
