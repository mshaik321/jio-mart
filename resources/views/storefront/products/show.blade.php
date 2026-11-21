@php
    $name = $product->translated('name');
    $alternateName = $product->alternateTranslated('name');
    $stock = $variant->totalStock();
    $mrp = (float) $variant->mrp;
    $price = (float) $variant->selling_price;
    $discount = $mrp > $price && $mrp > 0 ? (int) round(($mrp - $price) / $mrp * 100) : 0;
    $image = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
@endphp

<x-layouts.storefront :title="$name.' — GroceryHub'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="text-sm text-gray-500 mb-4" aria-label="{{ __('Breadcrumb') }}">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-emerald-700">{{ __('Home') }}</a>
            <span class="mx-1">/</span>
            <a href="{{ route('categories.show', $product->category->slug) }}" wire:navigate class="hover:text-emerald-700">{{ $product->category->translated('name') }}</a>
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $name }}</span>
        </nav>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="relative bg-white border border-gray-200 rounded-xl aspect-square flex items-center justify-center overflow-hidden">
                @if($discount > 0)
                    <span class="absolute left-3 top-3 rounded-md bg-amber-400 px-2 py-1 text-xs font-bold text-emerald-950">{{ __(':percent% OFF', ['percent' => $discount]) }}</span>
                @endif
                @if($image)
                    <img src="{{ $image->url() }}" alt="{{ $name }}" width="600" height="600" class="object-cover w-full h-full">
                @else
                    <span class="text-gray-400">{{ __('No image') }}</span>
                @endif
            </div>

            <div>
                @if($product->brand)
                    <p class="text-sm text-emerald-700 font-medium">{{ $product->brand->name }}</p>
                @endif
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">{{ $name }}</h1>
                @if($alternateName)
                    <p class="mt-1 text-lg text-gray-600">{{ $alternateName }}</p>
                @endif

                <div class="flex items-center gap-2 mt-2">
                    <span class="text-sm text-amber-500">&#9733; {{ $averageRating ?: '—' }}</span>
                    <span class="text-sm text-gray-400">({{ trans_choice(':count review|:count reviews', $reviews->total(), ['count' => $reviews->total()]) }})</span>
                </div>

                @if($product->translated('short_description'))
                    <p class="text-gray-600 mt-4">{{ $product->translated('short_description') }}</p>
                @endif

                @if($product->variants->count() > 1)
                    <div class="mt-5">
                        <p class="text-sm font-semibold text-gray-900 mb-2">{{ __('Select size/pack') }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($product->variants as $v)
                                <a href="{{ route('products.show', [$product->slug, 'variant' => $v->id]) }}"
                                   class="px-3 py-1.5 rounded-md border text-sm {{ $v->id === $variant->id ? 'border-emerald-700 bg-emerald-50 text-emerald-800 font-semibold' : 'border-gray-300 text-gray-700 hover:border-emerald-400' }}">
                                    {{ $v->variant_name }} &middot; ₹{{ number_format((float) $v->selling_price, 2) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-600">{{ __('Pack size') }}: <span class="font-semibold text-gray-900">{{ $variant->variant_name }}</span></p>
                @endif

                <div class="flex flex-wrap items-baseline gap-x-3 mt-5">
                    <span class="text-3xl font-bold text-gray-900">₹{{ number_format($price, 2) }}</span>
                    @if($discount > 0)
                        <span class="text-lg text-gray-400 line-through">₹{{ number_format($mrp, 2) }}</span>
                    @endif
                    <span class="text-sm text-gray-500">{{ __('Taxes applied at checkout') }}</span>
                </div>

                <p class="text-sm font-medium mt-2 {{ $stock <= 0 ? 'text-red-600' : ($stock <= 10 ? 'text-amber-600' : 'text-emerald-700') }}">
                    @if($stock <= 0)
                        {{ __('Out of stock') }}
                    @elseif($stock <= 10)
                        {{ __('Only :count left', ['count' => $stock]) }}
                    @else
                        {{ __('In stock') }}
                    @endif
                </p>

                <div class="mt-6 max-w-xs flex gap-3">
                    <div class="flex-1">
                        @if($stock > 0)
                            <livewire:storefront.add-to-cart-button :variant="$variant" :key="'atc-detail-'.$variant->id" />
                        @else
                            <button type="button" disabled class="h-9 w-full cursor-not-allowed rounded-md bg-gray-100 text-sm font-semibold text-gray-400">{{ __('Out of stock') }}</button>
                        @endif
                    </div>
                    <livewire:storefront.wishlist-button :variant="$variant" :key="'wish-'.$variant->id" />
                </div>

                @if($product->translated('description'))
                    <div class="mt-8 border-t border-gray-200 pt-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Product Details') }}</h2>
                        <p class="text-gray-600 whitespace-pre-line">{{ $product->translated('description') }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($related->isNotEmpty())
            <section class="mt-12" aria-labelledby="related-products">
                <h2 id="related-products" class="text-lg font-semibold text-gray-900 mb-4">{{ __('You may also like') }}</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                    @foreach($related as $relatedVariant)
                        <x-product-card :variant="$relatedVariant" />
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mt-12 border-t border-gray-200 pt-8 max-w-3xl">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Customer Reviews') }}</h2>

            @if(session('status'))
                <div class="bg-emerald-50 text-emerald-800 text-sm rounded-md p-3 mb-4">{{ session('status') }}</div>
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

            @auth
                <form action="{{ route('products.reviews.store', $product) }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-4 mb-6">
                    @csrf
                    <p class="text-sm font-medium text-gray-900 mb-2">{{ __('Write a review') }}</p>
                    <select name="rating" class="rounded-md border-gray-300 text-sm mb-2" required>
                        <option value="">{{ __('Rating') }}</option>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}">{{ trans_choice(':count star|:count stars', $i, ['count' => $i]) }}</option>
                        @endfor
                    </select>
                    <input type="text" name="title" placeholder="{{ __('Title (optional)') }}" class="w-full rounded-md border-gray-300 text-sm mb-2">
                    <textarea name="body" rows="3" placeholder="{{ __('Share your experience (optional)') }}" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                    <button type="submit" class="mt-2 bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2 rounded-md">{{ __('Submit Review') }}</button>
                </form>
            @endauth

            @forelse($reviews as $review)
                <div class="border-b border-gray-100 py-4">
                    <div class="flex items-center gap-2">
                        <span class="text-amber-500 text-sm">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                        @if($review->is_verified_purchase)
                            <span class="text-xs text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">{{ __('Verified Purchase') }}</span>
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
                <p class="text-gray-500 text-sm">{{ __('No reviews yet. Be the first to review this product.') }}</p>
            @endforelse

            {{ $reviews->links() }}
        </div>
    </div>
</x-layouts.storefront>
