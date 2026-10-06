<x-layouts.storefront :title="($term ? __('Search: :term', ['term' => $term]) : __('All Products')).' — GroceryHub'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">
            @if($term)
                {{ __('Search results for ":term"', ['term' => $term]) }}
            @else
                {{ __('All Products') }}
            @endif
        </h1>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <div class="lg:col-span-1">
                @include('storefront.search._filters')
            </div>

            <div class="lg:col-span-3">
                @if($variants->isEmpty())
                    <p class="text-gray-500">{{ __('No products matched your search.') }}</p>
                @else
                    <p class="mb-3 text-sm text-gray-500">{{ trans_choice(':count product|:count products', $variants->total(), ['count' => $variants->total()]) }}</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 mb-6">
                        @foreach($variants as $variant)
                            <x-product-card :variant="$variant" />
                        @endforeach
                    </div>
                    {{ $variants->links() }}
                @endif
            </div>
        </div>
    </div>
</x-layouts.storefront>
