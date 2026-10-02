<x-layouts.storefront :title="'GroceryHub — Fresh groceries, delivered'">
    <div class="bg-emerald-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <h1 class="text-3xl sm:text-4xl font-bold">Fresh groceries, delivered fast.</h1>
            <p class="mt-2 text-emerald-100">Staples, fruits &amp; vegetables, snacks and household essentials — all in one place.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Shop by Category</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4 mb-10">
            @foreach($categories as $category)
                <a href="{{ route('categories.show', $category->slug) }}" wire:navigate class="bg-white rounded-lg border border-gray-200 p-4 text-center hover:shadow-md transition-shadow">
                    <div class="h-16 flex items-center justify-center text-emerald-700 font-bold text-2xl">
                        {{ mb_substr($category->name, 0, 1) }}
                    </div>
                    <p class="text-sm font-medium text-gray-800 mt-2">{{ $category->name }}</p>
                </a>
            @endforeach
        </div>

        @if($featured->isNotEmpty())
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Featured Products</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-10">
                @foreach($featured as $variant)
                    <x-product-card :variant="$variant" />
                @endforeach
            </div>
        @endif

        @if($bestsellers->isNotEmpty())
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Bestsellers</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-10">
                @foreach($bestsellers as $variant)
                    <x-product-card :variant="$variant" />
                @endforeach
            </div>
        @endif

        @if($newArrivals->isNotEmpty())
            <h2 class="text-lg font-semibold text-gray-900 mb-4">New Arrivals</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($newArrivals as $variant)
                    <x-product-card :variant="$variant" />
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.storefront>
