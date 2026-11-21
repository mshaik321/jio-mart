<x-layouts.admin :title="$orderReturn->return_number">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $orderReturn->return_number }}</h2>
            <p class="text-sm text-gray-500">Order {{ $orderReturn->order->order_number }} &middot; {{ $orderReturn->customer->user->name ?? '—' }}</p>
        </div>
        <span class="text-sm px-3 py-1 rounded-full bg-gray-100 text-gray-700 font-medium">{{ ucfirst($orderReturn->status->value) }}</span>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-5 mb-6">
        <h3 class="font-semibold text-gray-900 mb-3">Reason</h3>
        <p class="text-sm text-gray-700">{{ $orderReturn->reason }}</p>
        @if($orderReturn->note)
            <p class="text-sm text-gray-500 mt-1">Note: {{ $orderReturn->note }}</p>
        @endif
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-5 mb-6">
        <h3 class="font-semibold text-gray-900 mb-3">Items</h3>
        @foreach($orderReturn->items as $item)
            <div class="flex justify-between text-sm py-1.5 border-b border-gray-100 last:border-0">
                <span>{{ $item->orderItem->product_name }} ({{ $item->orderItem->variant_name }}) &times; {{ $item->quantity }}</span>
                <span class="text-gray-500">{{ $item->reason }}</span>
            </div>
        @endforeach
    </div>

    <div class="flex gap-3">
        @can('returns.manage')
            @if($orderReturn->status->value === 'requested')
                <form action="{{ route('admin.returns.approve', $orderReturn) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-fresh-600 hover:bg-fresh-700 text-white text-sm font-semibold px-4 py-2 rounded-md">Approve</button>
                </form>
                <form action="{{ route('admin.returns.reject', $orderReturn) }}" method="POST">
                    @csrf
                    <button type="submit" class="border border-red-300 text-red-700 text-sm font-semibold px-4 py-2 rounded-md">Reject</button>
                </form>
            @elseif(in_array($orderReturn->status->value, ['approved', 'picked_up', 'inspected']))
                <form action="{{ route('admin.returns.complete', $orderReturn) }}" method="POST" class="flex gap-2 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Refund Amount</label>
                        <input type="number" step="0.01" name="refund_amount" value="{{ $orderReturn->order->grand_total }}" class="rounded-md border-gray-300 text-sm">
                    </div>
                    <button type="submit" class="bg-fresh-600 hover:bg-fresh-700 text-white text-sm font-semibold px-4 py-2 rounded-md">Complete &amp; Restock</button>
                </form>
            @endif
        @endcan
    </div>
</x-layouts.admin>
