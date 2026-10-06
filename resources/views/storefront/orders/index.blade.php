<x-layouts.storefront :title="__('My Orders').' — GroceryHub'">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('My Orders') }}</h1>

        @if(session('status'))
            <div class="bg-emerald-50 text-emerald-800 text-sm rounded-md p-3 mb-4">{{ session('status') }}</div>
        @endif

        @if($orders->isEmpty())
            <p class="text-gray-500">{{ __('You haven\'t placed any orders yet.') }}</p>
        @else
            <div class="space-y-3">
                @foreach($orders as $order)
                    <a href="{{ route('orders.show', $order) }}" wire:navigate class="block bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $order->order_number }}</p>
                                <p class="text-xs text-gray-500">{{ $order->created_at->format('d M Y, h:i A') }} &middot; {{ trans_choice(':count item|:count items', $order->items->count(), ['count' => $order->items->count()]) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-900">₹{{ number_format((float) $order->grand_total, 2) }}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">{{ $order->status->label() }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{ $orders->links() }}
        @endif
    </div>
</x-layouts.storefront>
