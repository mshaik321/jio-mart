<x-layouts.storefront :title="$order->order_number.' — GroceryHub'">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('status'))
            <div class="bg-fresh-50 text-fresh-800 text-sm rounded-md p-3 mb-4">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 text-red-700 text-sm rounded-md p-3 mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $order->order_number }}</h1>
                <p class="text-sm text-gray-500">{{ __('Placed on :date', ['date' => $order->placed_at?->format('d M Y, h:i A')]) }}</p>
            </div>
            <span class="text-sm px-3 py-1 rounded-full bg-gray-100 text-gray-700 font-medium">{{ $order->status->label() }}</span>
        </div>

        @if($order->delivery)
            <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 text-sm">
                <p class="font-semibold text-gray-900">{{ __('Delivery') }}: {{ __(ucfirst(str_replace('_', ' ', $order->delivery->status->value))) }}</p>
                @if($order->delivery->agent)
                    <p class="mt-1 text-gray-600">{{ __('Delivery partner') }}: {{ $order->delivery->agent->name }}</p>
                @endif
            </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-lg p-5 mb-6">
            <h2 class="font-semibold text-gray-900 mb-3">{{ __('Items') }}</h2>
            @foreach($order->items as $item)
                <div class="flex justify-between text-sm py-1.5 border-b border-gray-100 last:border-0">
                    <span>{{ $item->product_name }} ({{ $item->variant_name }}) &times; {{ $item->quantity }}</span>
                    <span>₹{{ number_format((float) $item->total, 2) }}</span>
                </div>
            @endforeach
            <div class="flex justify-between font-bold text-gray-900 mt-3 pt-3 border-t border-gray-200">
                <span>{{ __('Total') }}</span>
                <span>₹{{ number_format((float) $order->grand_total, 2) }}</span>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-5 mb-6">
            <h2 class="font-semibold text-gray-900 mb-3">{{ __('Delivery Address') }}</h2>
            <p class="text-sm text-gray-700">{{ $order->shipping_recipient_name }} &middot; {{ $order->shipping_phone }}</p>
            <p class="text-sm text-gray-700">{{ $order->shipping_line1 }}, {{ $order->shipping_line2 }}</p>
            <p class="text-sm text-gray-700">{{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_pincode }}</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-5 mb-6">
            <h2 class="font-semibold text-gray-900 mb-3">{{ __('Order Timeline') }}</h2>
            <ul class="space-y-2">
                @foreach($order->statusHistories as $history)
                    <li class="text-sm text-gray-600">
                        <span class="font-medium text-gray-900">{{ $history->status->label() }}</span>
                        — {{ $history->created_at->format('d M Y, h:i A') }}
                        @if($history->note) <span class="text-gray-400">({{ $history->note }})</span> @endif
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="flex gap-3">
            @if($order->status->isCancellable())
                <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm(@js(__('Cancel this order?')));">
                    @csrf
                    <button type="submit" class="border border-red-300 text-red-700 font-semibold px-4 py-2 rounded-md text-sm">{{ __('Cancel Order') }}</button>
                </form>
            @endif

            @if($order->status->value === 'delivered' && ! $order->return)
                <details class="inline-block">
                    <summary class="cursor-pointer border border-gray-300 text-gray-700 font-semibold px-4 py-2 rounded-md text-sm inline-block">{{ __('Request Return') }}</summary>
                    <form action="{{ route('orders.return.store', $order) }}" method="POST" class="mt-3 bg-white border border-gray-200 rounded-lg p-4 max-w-md">
                        @csrf
                        <p class="text-sm font-medium text-gray-900 mb-2">{{ __('Select items to return') }}</p>
                        @foreach($order->items as $item)
                            <label class="flex items-center gap-2 text-sm mb-1">
                                <input type="checkbox" name="items[{{ $loop->index }}][order_item_id]" value="{{ $item->id }}">
                                {{ $item->product_name }} ({{ $item->variant_name }}) &times; {{ $item->quantity }}
                                <input type="hidden" name="items[{{ $loop->index }}][quantity]" value="{{ $item->quantity }}">
                            </label>
                        @endforeach
                        <input type="text" name="reason" placeholder="{{ __('Reason for return') }}" class="w-full rounded-md border-gray-300 text-sm mt-2" required>
                        <button type="submit" class="mt-3 bg-fresh-600 hover:bg-fresh-700 text-white text-sm font-semibold px-4 py-2 rounded-md">{{ __('Submit Return Request') }}</button>
                    </form>
                </details>
            @endif
        </div>
    </div>
</x-layouts.storefront>
