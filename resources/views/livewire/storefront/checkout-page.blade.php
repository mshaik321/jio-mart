    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Checkout</h1>

        @if($error)
            <div class="bg-red-50 text-red-700 text-sm rounded-md p-3 mb-4">{{ $error }}</div>
        @endif

        @if($cart->items->isEmpty())
            <p class="text-gray-500">Your cart is empty. <a href="{{ route('home') }}" wire:navigate class="text-emerald-700 underline">Start shopping</a>.</p>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white border border-gray-200 rounded-lg p-5">
                        <h2 class="font-semibold text-gray-900 mb-3">Delivery Address</h2>

                        @if($addresses->isEmpty())
                            <p class="text-sm text-gray-500 mb-3">You have no saved addresses yet.</p>
                            <a href="{{ route('addresses.create') }}" class="text-sm text-emerald-700 underline">Add an address</a>
                        @else
                            <div class="space-y-2">
                                @foreach($addresses as $address)
                                    <label class="flex items-start gap-3 border rounded-md p-3 cursor-pointer {{ $addressId === $address->id ? 'border-emerald-600 bg-emerald-50' : 'border-gray-200' }}">
                                        <input type="radio" wire:model.live="addressId" value="{{ $address->id }}" class="mt-1">
                                        <span class="text-sm">
                                            <span class="font-medium text-gray-900">{{ $address->label }} — {{ $address->recipient_name }}</span><br>
                                            {{ $address->line1 }}, {{ $address->line2 }}<br>
                                            {{ $address->city }}, {{ $address->state }} {{ $address->pincode }}<br>
                                            {{ $address->phone }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <a href="{{ route('addresses.create') }}" class="inline-block mt-3 text-sm text-emerald-700 underline">+ Add new address</a>
                        @endif

                        @if($addressId && ! $zone)
                            <p class="text-sm text-red-600 mt-3">Sorry, we don't deliver to this address's pincode yet.</p>
                        @endif
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg p-5">
                        <h2 class="font-semibold text-gray-900 mb-3">Delivery Slot</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($slots as $slot)
                                <label class="px-3 py-2 border rounded-md text-sm cursor-pointer {{ $deliverySlotId === $slot->id ? 'border-emerald-600 bg-emerald-50' : 'border-gray-200' }}">
                                    <input type="radio" wire:model.live="deliverySlotId" value="{{ $slot->id }}" class="hidden">
                                    {{ $slot->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg p-5">
                        <h2 class="font-semibold text-gray-900 mb-3">Payment Method</h2>
                        <label class="flex items-center gap-2 text-sm mb-2">
                            <input type="radio" wire:model.live="paymentMethod" value="cod"> Cash on Delivery
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="radio" wire:model.live="paymentMethod" value="online"> Online Payment (coming soon)
                        </label>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-5 h-fit">
                    <h2 class="font-semibold text-gray-900 mb-4">Order Summary</h2>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-600">Subtotal</dt><dd>₹{{ number_format($totals['subtotal'], 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-600">Tax</dt><dd>₹{{ number_format($totals['tax_amount'], 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-600">Delivery</dt><dd>₹{{ number_format($deliveryCharge, 2) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-600">Discount</dt><dd class="text-emerald-700">−₹{{ number_format($totals['discount'], 2) }}</dd></div>
                    </dl>
                    <div class="flex justify-between font-bold text-gray-900 border-t border-gray-200 mt-3 pt-3">
                        <span>Total</span>
                        <span>₹{{ number_format($grandTotal, 2) }}</span>
                    </div>

                    <button wire:click="placeOrder" wire:loading.attr="disabled"
                        class="mt-4 w-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-2.5 rounded-md disabled:opacity-60">
                        Place Order
                    </button>
                </div>
            </div>
        @endif
    </div>
