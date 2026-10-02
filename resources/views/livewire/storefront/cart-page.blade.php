<x-layouts.storefront :title="'My Cart — GroceryHub'">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">My Cart</h1>

        @if($error)
            <div class="bg-red-50 text-red-700 text-sm rounded-md p-3 mb-4">{{ $error }}</div>
        @endif

        @if($cart->items->isEmpty())
            <p class="text-gray-500">Your cart is empty. <a href="{{ route('home') }}" wire:navigate class="text-emerald-700 underline">Start shopping</a>.</p>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-3">
                    @foreach($cart->items as $item)
                        <div class="bg-white border border-gray-200 rounded-lg p-4 flex gap-4 items-center">
                            <div class="h-16 w-16 bg-gray-100 rounded-md flex-shrink-0 flex items-center justify-center overflow-hidden">
                                @if($item->variant->product->primaryImage)
                                    <img src="{{ $item->variant->product->primaryImage->url() }}" class="object-cover w-full h-full">
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $item->variant->product->name }}</p>
                                <p class="text-xs text-gray-500">{{ $item->variant->variant_name }}</p>
                                <p class="text-sm text-gray-700 mt-1">₹{{ number_format((float) $item->price_snapshot, 2) }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button wire:click="updateQuantity({{ $item->product_variant_id }}, {{ $item->quantity - 1 }})" class="h-8 w-8 rounded border border-gray-300 text-gray-600">−</button>
                                <span class="w-8 text-center">{{ $item->quantity }}</span>
                                <button wire:click="updateQuantity({{ $item->product_variant_id }}, {{ $item->quantity + 1 }})" class="h-8 w-8 rounded border border-gray-300 text-gray-600">+</button>
                            </div>
                            <p class="w-24 text-right font-semibold text-gray-900">₹{{ number_format((float) $item->price_snapshot * $item->quantity, 2) }}</p>
                            <button wire:click="removeItem({{ $item->product_variant_id }})" class="text-gray-400 hover:text-red-600 text-sm">Remove</button>
                        </div>
                    @endforeach
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-5 h-fit">
                    <h2 class="font-semibold text-gray-900 mb-4">Order Summary</h2>

                    @if($cart->coupon)
                        <div class="flex items-center justify-between text-sm mb-2 bg-emerald-50 text-emerald-800 rounded-md px-3 py-2">
                            <span>Coupon: {{ $cart->coupon->code }}</span>
                            <button wire:click="removeCoupon" class="underline">Remove</button>
                        </div>
                    @else
                        <form wire:submit.prevent="applyCoupon" class="flex gap-2 mb-4">
                            <input type="text" wire:model="couponCode" placeholder="Coupon code" class="flex-1 rounded-md border-gray-300 text-sm">
                            <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm px-3 rounded-md">Apply</button>
                        </form>
                    @endif

                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-600">Subtotal</dt><dd>₹{{ number_format($totals['subtotal'], 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-600">Tax</dt><dd>₹{{ number_format($totals['tax_amount'], 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-600">Discount</dt><dd class="text-emerald-700">−₹{{ number_format($totals['discount'], 2) }}</dd></div>
                        <div class="flex justify-between text-xs text-gray-400"><dt>Delivery</dt><dd>Calculated at checkout</dd></div>
                    </dl>

                    <div class="flex justify-between font-bold text-gray-900 border-t border-gray-200 mt-3 pt-3">
                        <span>Estimated Total</span>
                        <span>₹{{ number_format($totals['subtotal'] + $totals['tax_amount'] - $totals['discount'], 2) }}</span>
                    </div>

                    <a href="{{ route('checkout.index') }}" wire:navigate class="mt-4 block text-center bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-2.5 rounded-md">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-layouts.storefront>
