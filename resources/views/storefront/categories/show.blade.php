<x-layouts.storefront :title="$category->name.' — GroceryHub'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ $category->name }}</h1>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <div class="lg:col-span-1">
                @include('storefront.search._filters')
            </div>

            <div class="lg:col-span-3">
                @if($variants->isEmpty())
                    <p class="text-gray-500">No products found in this category yet.</p>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
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
