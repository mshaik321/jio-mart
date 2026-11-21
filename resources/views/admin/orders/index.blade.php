<x-layouts.admin title="Orders">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Orders</h2>

    <form method="GET" class="mb-4 flex gap-3">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Order number..." class="rounded-md border-gray-300 text-sm w-64">
        <select name="status" aria-label="Filter by status" onchange="this.form.submit()" class="rounded-md border-gray-300 text-sm">
            <option value="">All statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </form>

    <div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">Order #</th><th class="px-4 py-2">Customer</th><th class="px-4 py-2">Total</th><th class="px-4 py-2">Payment</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th></tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $order->order_number }}</td>
                        <td class="px-4 py-2">{{ $order->customer->user->name ?? '—' }}</td>
                        <td class="px-4 py-2">₹{{ number_format((float) $order->grand_total, 2) }}</td>
                        <td class="px-4 py-2">{{ ucfirst($order->payment_status->value) }}</td>
                        <td class="px-4 py-2">{{ $order->status->label() }}</td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-brand-700 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</x-layouts.admin>
