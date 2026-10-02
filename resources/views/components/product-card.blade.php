@props(['variant'])

@php
    $product = $variant->product;
    $image = $product->primaryImage?->url();
    $inStock = $variant->totalStock() > 0;
@endphp

<div class="group bg-white rounded-lg border border-gray-200 hover:shadow-md transition-shadow flex flex-col">
    <a href="{{ route('products.show', $product->slug) }}" wire:navigate class="block p-4">
        <div class="aspect-square bg-gray-100 rounded-md flex items-center justify-center overflow-hidden mb-3">
            @if($image)
                <img src="{{ $image }}" alt="{{ $product->name }}" class="object-cover w-full h-full">
            @else
                <span class="text-gray-400 text-sm">No image</span>
            @endif
        </div>
        <p class="text-xs text-gray-500">{{ $variant->variant_name }}</p>
        <h3 class="text-sm font-medium text-gray-900 line-clamp-2 group-hover:text-emerald-700">{{ $product->name }}</h3>
    </a>

    <div class="px-4 pb-4 mt-auto">
        <div class="flex items-baseline gap-2 mb-2">
            <span class="text-base font-bold text-gray-900">₹{{ number_format((float) $variant->selling_price, 2) }}</span>
            @if((float) $variant->mrp > (float) $variant->selling_price)
                <span class="text-xs text-gray-400 line-through">₹{{ number_format((float) $variant->mrp, 2) }}</span>
            @endif
        </div>

        @if($inStock)
            <livewire:storefront.add-to-cart-button :variant="$variant" :key="'atc-'.$variant->id" />
        @else
            <button disabled class="w-full bg-gray-200 text-gray-500 text-sm font-semibold py-2 rounded-md cursor-not-allowed">
                Out of Stock
            </button>
        @endif
    </div>
</div>
