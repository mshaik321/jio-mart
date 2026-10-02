<x-layouts.admin title="Dashboard">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Today's Orders</p>
            <p class="text-2xl font-bold text-gray-900">{{ $todaysOrders }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Today's Sales</p>
            <p class="text-2xl font-bold text-gray-900">₹{{ number_format($todaysSales, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Pending Orders</p>
            <p class="text-2xl font-bold text-amber-600">{{ $pendingOrders }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Delivered Orders</p>
            <p class="text-2xl font-bold text-emerald-700">{{ $deliveredOrders }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Cancelled Orders</p>
            <p class="text-2xl font-bold text-red-600">{{ $cancelledOrders }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Customers</p>
            <p class="text-2xl font-bold text-gray-900">{{ $totalCustomers }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4 col-span-2">
            <p class="text-xs text-gray-500">Total Revenue (non-cancelled)</p>
            <p class="text-2xl font-bold text-gray-900">₹{{ number_format($totalRevenue, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Recent Orders</h2>
            <table class="w-full text-sm">
                <thead class="text-left text-gray-500">
                    <tr><th class="pb-2">Order #</th><th class="pb-2">Customer</th><th class="pb-2">Total</th><th class="pb-2">Status</th></tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                        <tr class="border-t border-gray-100">
                            <td class="py-2"><a href="{{ route('admin.orders.show', $order) }}" class="text-emerald-700 hover:underline">{{ $order->order_number }}</a></td>
                            <td class="py-2">{{ $order->customer->user->name ?? '—' }}</td>
                            <td class="py-2">₹{{ number_format((float) $order->grand_total, 2) }}</td>
                            <td class="py-2">{{ $order->status->label() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-3">Low Stock Alerts</h2>
            @forelse($lowStock as $inventory)
                <div class="flex justify-between text-sm border-t border-gray-100 py-2 first:border-0">
                    <span>{{ $inventory->variant->product->name }} ({{ $inventory->variant->variant_name }})</span>
                    <span class="font-semibold text-red-600">{{ $inventory->availableQuantity() }} left</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">No low-stock products.</p>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
