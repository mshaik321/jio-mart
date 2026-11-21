@props(['variant'])

@php
    $product = $variant->product;
    $image = $product->primaryImage?->url();
    $stock = $variant->totalStock();
    $name = $product->translated('name');
    $alternateName = $product->alternateTranslated('name');
    $mrp = (float) $variant->mrp;
    $price = (float) $variant->selling_price;
    $discount = $mrp > $price && $mrp > 0 ? (int) round(($mrp - $price) / $mrp * 100) : 0;
@endphp

<div class="group relative flex flex-col rounded-xl border border-gray-200 bg-white transition-shadow hover:shadow-md">
    @if($discount > 0)
        <span class="absolute left-2 top-2 z-10 rounded-md bg-sun-300 px-1.5 py-0.5 text-[11px] font-bold text-gray-900">{{ __(':percent% OFF', ['percent' => $discount]) }}</span>
    @endif

    <a href="{{ route('products.show', $product->slug) }}" wire:navigate class="block p-3 sm:p-4">
        <div class="mb-3 aspect-square overflow-hidden rounded-lg bg-gray-50">
            @if($image)
                <img src="{{ $image }}" alt="{{ $name }}" loading="lazy" width="600" height="600" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
            @else
                <div class="flex h-full items-center justify-center text-sm text-gray-400">{{ __('No image') }}</div>
            @endif
        </div>
        <h3 class="line-clamp-2 text-sm font-semibold leading-snug text-gray-900 group-hover:text-brand-800">{{ $name }}</h3>
        @if($alternateName)
            <p class="mt-0.5 line-clamp-1 text-xs text-gray-500">{{ $alternateName }}</p>
        @endif
        <p class="mt-1 text-xs font-medium text-gray-500">{{ $variant->variant_name }}</p>
    </a>

    <div class="mt-auto px-3 pb-3 sm:px-4 sm:pb-4">
        <div class="flex flex-wrap items-baseline gap-x-1.5">
            <span class="text-base font-bold text-gray-900">₹{{ number_format($price, 2) }}</span>
            @if($discount > 0)
                <span class="text-xs text-gray-500 line-through">₹{{ number_format($mrp, 2) }}</span>
            @endif
        </div>

        @if($stock <= 0)
            <p class="mb-2 text-xs font-medium text-red-600">{{ __('Out of stock') }}</p>
            <button type="button" disabled class="h-9 w-full cursor-not-allowed rounded-md bg-gray-100 text-sm font-semibold text-gray-400">
                {{ __('Out of stock') }}
            </button>
        @else
            <p class="mb-2 text-xs font-medium {{ $stock <= 10 ? 'text-amber-700' : 'text-fresh-700' }}">
                {{ $stock <= 10 ? __('Only :count left', ['count' => $stock]) : __('In stock') }}
            </p>
            <livewire:storefront.add-to-cart-button :variant="$variant" :key="'atc-'.$variant->id" />
        @endif
    </div>
</div>
