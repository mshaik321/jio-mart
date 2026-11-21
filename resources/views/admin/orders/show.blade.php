<x-layouts.admin :title="$order->order_number">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $order->order_number }}</h2>
            <p class="text-sm text-gray-500">{{ $order->customer->user->name ?? '—' }} &middot; {{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
        <span class="text-sm px-3 py-1 rounded-full bg-gray-100 text-gray-700 font-medium">{{ $order->status->label() }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Items</h3>
                @foreach($order->items as $item)
                    <div class="flex justify-between text-sm py-1.5 border-b border-gray-100 last:border-0">
                        <span>{{ $item->product_name }} ({{ $item->variant_name }}) &times; {{ $item->quantity }}</span>
                        <span>₹{{ number_format((float) $item->total, 2) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between font-bold text-gray-900 mt-3 pt-3 border-t border-gray-200">
                    <span>Total</span>
                    <span>₹{{ number_format((float) $order->grand_total, 2) }}</span>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Shipping Address</h3>
                <p class="text-sm text-gray-700">{{ $order->shipping_recipient_name }} &middot; {{ $order->shipping_phone }}</p>
                <p class="text-sm text-gray-700">{{ $order->shipping_line1 }}, {{ $order->shipping_line2 }}</p>
                <p class="text-sm text-gray-700">{{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_pincode }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-5">
                <h3 class="font-semibold text-gray-900 mb-3">Status History</h3>
                <ul class="space-y-1">
                    @foreach($order->statusHistories as $history)
                        <li class="text-sm text-gray-600">
                            <span class="font-medium text-gray-900">{{ $history->status->label() }}</span>
                            — {{ $history->created_at->format('d M Y, h:i A') }}
                            @if($history->changedBy) by {{ $history->changedBy->name }} @endif
                            @if($history->note) <span class="text-gray-400">({{ $history->note }})</span> @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            @can('orders.update')
                <div class="bg-white border border-gray-200 rounded-lg p-5">
                    <h3 class="font-semibold text-gray-900 mb-3">Update Status</h3>
                    <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                        @csrf
                        <select name="status" class="w-full rounded-md border-gray-300 text-sm mb-2">
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" @selected($order->status === $status)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="note" placeholder="Note (optional)" class="w-full rounded-md border-gray-300 text-sm mb-2">
                        <button type="submit" class="w-full bg-fresh-600 hover:bg-fresh-700 text-white text-sm font-semibold py-2 rounded-md">Update Status</button>
                    </form>
                </div>
            @endcan

            @can('delivery.manage')
                <div class="bg-white border border-gray-200 rounded-lg p-5">
                    <h3 class="font-semibold text-gray-900 mb-3">Delivery</h3>
                    @if($order->delivery?->agent)
                        <p class="text-sm text-gray-700 mb-2">Assigned to: {{ $order->delivery->agent->name }}</p>
                    @endif
                    <form action="{{ route('admin.orders.delivery.assign', $order) }}" method="POST">
                        @csrf
                        <select name="delivery_agent_id" class="w-full rounded-md border-gray-300 text-sm mb-2" required>
                            <option value="">Select agent</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }} ({{ $agent->phone }})</option>
                            @endforeach
                        </select>
                        <button type="submit" class="w-full bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold py-2 rounded-md">Assign Agent</button>
                    </form>
                    @if($order->delivery)
                        <form action="{{ route('admin.orders.delivery.update-status', $order) }}" method="POST" class="mt-4 pt-4 border-t border-gray-100">
                            @csrf
                            <label for="delivery-status" class="block text-xs text-gray-600 mb-1">Delivery status</label>
                            <select id="delivery-status" name="status" class="w-full rounded-md border-gray-300 text-sm mb-2">
                                @foreach(\App\Enums\DeliveryStatus::cases() as $deliveryStatus)
                                    <option value="{{ $deliveryStatus->value }}" @selected($order->delivery->status === $deliveryStatus)>{{ ucfirst(str_replace('_', ' ', $deliveryStatus->value)) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="w-full border border-gray-300 text-gray-800 text-sm font-semibold py-2 rounded-md hover:bg-gray-50">Update Delivery Status</button>
                        </form>
                    @endif
                </div>
            @endcan

            @can('orders.cancel')
                @if($order->status->isCancellable())
                    <form action="{{ route('admin.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Cancel this order?');">
                        @csrf
                        <button type="submit" class="w-full border border-red-300 text-red-700 font-semibold py-2 rounded-md text-sm">Cancel Order</button>
                    </form>
                @endif
            @endcan
        </div>
    </div>
</x-layouts.admin>
