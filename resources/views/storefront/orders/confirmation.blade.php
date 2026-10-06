<x-layouts.storefront :title="__('Order Confirmed').' — GroceryHub'">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
        <div class="text-emerald-600 text-5xl mb-4">&check;</div>
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Order placed successfully!') }}</h1>
        <p class="text-gray-600 mt-2">{{ __('Order :number has been confirmed.', ['number' => $order->order_number]) }}</p>

        <div class="bg-white border border-gray-200 rounded-lg p-5 mt-6 text-left">
            @foreach($order->items as $item)
                <div class="flex justify-between text-sm py-1">
                    <span>{{ $item->product_name }} ({{ $item->variant_name }}) &times; {{ $item->quantity }}</span>
                    <span>₹{{ number_format((float) $item->total, 2) }}</span>
                </div>
            @endforeach
            <div class="flex justify-between font-bold text-gray-900 border-t border-gray-200 mt-3 pt-3">
                <span>{{ __('Total') }}</span>
                <span>₹{{ number_format((float) $order->grand_total, 2) }}</span>
            </div>
        </div>

        <div class="mt-6 flex justify-center gap-3">
            <a href="{{ route('orders.show', $order) }}" wire:navigate class="bg-emerald-700 hover:bg-emerald-800 text-white font-semibold px-5 py-2.5 rounded-md">{{ __('Track Order') }}</a>
            <a href="{{ route('home') }}" wire:navigate class="border border-gray-300 text-gray-700 font-semibold px-5 py-2.5 rounded-md">{{ __('Continue Shopping') }}</a>
        </div>
    </div>
</x-layouts.storefront>
