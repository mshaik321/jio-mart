<x-layouts.storefront :title="$category->translated('name').' — GroceryHub'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <nav class="text-sm text-gray-500 mb-3" aria-label="{{ __('Breadcrumb') }}">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-brand-800">{{ __('Home') }}</a>
            <span class="mx-1">/</span>
            <span class="text-gray-900">{{ $category->translated('name') }}</span>
        </nav>

        <div class="mb-6 flex items-center gap-4">
            @if($category->imageUrl())
                <img src="{{ $category->imageUrl() }}" alt="" width="64" height="64" class="h-14 w-14 sm:h-16 sm:w-16 rounded-xl object-cover border border-gray-200">
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $category->translated('name') }}</h1>
                @if($category->alternateTranslated('name'))
                    <p class="text-sm text-gray-500">{{ $category->alternateTranslated('name') }}</p>
                @endif
                @if($category->translated('description'))
                    <p class="mt-1 text-sm text-gray-600">{{ $category->translated('description') }}</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <div class="lg:col-span-1">
                @include('storefront.search._filters')
            </div>

            <div class="lg:col-span-3">
                @if($variants->isEmpty())
                    <p class="text-gray-500">{{ __('No products found in this category yet.') }}</p>
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
