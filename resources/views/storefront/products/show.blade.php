<x-layouts.storefront :title="$product->name.' — GroceryHub'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="text-sm text-gray-500 mb-4">
            <a href="{{ route('categories.show', $product->category->slug) }}" wire:navigate class="hover:text-emerald-700">{{ $product->category->name }}</a>
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $product->name }}</span>
        </nav>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white border border-gray-200 rounded-lg aspect-square flex items-center justify-center overflow-hidden">
                @if($product->primaryImage)
                    <img src="{{ $product->primaryImage->url() }}" alt="{{ $product->name }}" class="object-cover w-full h-full">
                @else
                    <span class="text-gray-400">No image</span>
                @endif
            </div>

            <div>
                @if($product->brand)
                    <p class="text-sm text-emerald-700 font-medium">{{ $product->brand->name }}</p>
                @endif
                <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $product->name }}</h1>

                <div class="flex items-center gap-2 mt-2">
                    <span class="text-sm text-amber-500">&#9733; {{ $averageRating ?: '—' }}</span>
                    <span class="text-sm text-gray-400">({{ $product->approvedReviews()->count() }} reviews)</span>
                </div>

                <p class="text-gray-600 mt-4">{{ $product->short_description }}</p>

                @if($product->variants->count() > 1)
                    <div class="mt-5">
                        <p class="text-sm font-semibold text-gray-900 mb-2">Select size/pack</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($product->variants as $v)
                                <a href="{{ route('products.show', [$product->slug, 'variant' => $v->id]) }}"
                                   class="px-3 py-1.5 rounded-md border text-sm {{ $v->id === $variant->id ? 'border-emerald-700 bg-emerald-50 text-emerald-800 font-semibold' : 'border-gray-300 text-gray-700 hover:border-emerald-400' }}">
                                    {{ $v->variant_name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex items-baseline gap-3 mt-5">
                    <span class="text-3xl font-bold text-gray-900">₹{{ number_format((float) $variant->selling_price, 2) }}</span>
                    @if((float) $variant->mrp > (float) $variant->selling_price)
                        <span class="text-lg text-gray-400 line-through">₹{{ number_format((float) $variant->mrp, 2) }}</span>
                    @endif
                    <span class="text-sm text-gray-500">incl. of all taxes</span>
                </div>

                <p class="text-sm mt-2 {{ $variant->totalStock() > 0 ? 'text-emerald-700' : 'text-red-600' }}">
                    {{ $variant->totalStock() > 0 ? 'In stock' : 'Out of stock' }}
                </p>

                <div class="mt-6 max-w-xs flex gap-3">
                    <div class="flex-1">
                        <livewire:storefront.add-to-cart-button :variant="$variant" :key="'atc-detail-'.$variant->id" />
                    </div>
                    <livewire:storefront.wishlist-button :variant="$variant" :key="'wish-'.$variant->id" />
                </div>

                @if($product->description)
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Product Details</h2>
                        <p class="text-gray-600 whitespace-pre-line">{{ $product->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-12 border-t border-gray-200 pt-8 max-w-3xl">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer Reviews</h2>

            @auth
                <form action="{{ route('products.reviews.store', $product) }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
                    @csrf
                    <p class="text-sm font-medium text-gray-900 mb-2">Write a review</p>
                    <select name="rating" class="rounded-md border-gray-300 text-sm mb-2" required>
                        <option value="">Rating</option>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                        @endfor
                    </select>
                    <input type="text" name="title" placeholder="Title (optional)" class="w-full rounded-md border-gray-300 text-sm mb-2">
                    <textarea name="body" rows="3" placeholder="Share your experience (optional)" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                    <button type="submit" class="mt-2 bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2 rounded-md">Submit Review</button>
                </form>
            @endauth

            @forelse($reviews as $review)
                <div class="border-b border-gray-100 py-4">
                    <div class="flex items-center gap-2">
                        <span class="text-amber-500 text-sm">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                        @if($review->is_verified_purchase)
                            <span class="text-xs text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">Verified Purchase</span>
                        @endif
                    </div>
                    @if($review->title)
                        <p class="font-medium text-gray-900 mt-1">{{ $review->title }}</p>
                    @endif
                    @if($review->body)
                        <p class="text-gray-600 text-sm mt-1">{{ $review->body }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-1">{{ $review->customer->user->name }} &middot; {{ $review->created_at->format('d M Y') }}</p>
                </div>
            @empty
                <p class="text-gray-500 text-sm">No reviews yet. Be the first to review this product.</p>
            @endforelse

            {{ $reviews->links() }}
        </div>
    </div>
</x-layouts.storefront>
