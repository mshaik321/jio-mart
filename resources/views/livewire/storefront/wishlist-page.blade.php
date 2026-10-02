    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">My Wishlist</h1>

        @if($error)
            <div class="bg-red-50 text-red-700 text-sm rounded-md p-3 mb-4">{{ $error }}</div>
        @endif

        @guest
            <p class="text-gray-500">Please <a href="{{ route('login') }}" class="text-emerald-700 underline">log in</a> to view your wishlist.</p>
        @else
            @if($items->isEmpty())
                <p class="text-gray-500">Your wishlist is empty.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($items as $item)
                        <div class="bg-white border border-gray-200 rounded-lg p-4 flex gap-4">
                            <div class="h-20 w-20 bg-gray-100 rounded-md flex-shrink-0 flex items-center justify-center overflow-hidden">
                                @if($item->variant->product->primaryImage)
                                    <img src="{{ $item->variant->product->primaryImage->url() }}" class="object-cover w-full h-full">
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $item->variant->product->name }}</p>
                                <p class="text-xs text-gray-500">{{ $item->variant->variant_name }}</p>
                                <p class="font-bold text-gray-900 mt-1">₹{{ number_format((float) $item->variant->selling_price, 2) }}</p>
                                <div class="flex gap-2 mt-2">
                                    <button wire:click="moveToCart({{ $item->id }})" class="text-xs bg-emerald-700 hover:bg-emerald-800 text-white px-3 py-1.5 rounded-md">Move to Cart</button>
                                    <button wire:click="remove({{ $item->id }})" class="text-xs text-gray-500 hover:text-red-600 px-3 py-1.5">Remove</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endguest
    </div>
