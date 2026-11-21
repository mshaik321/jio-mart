<x-layouts.storefront :title="__('GroceryHub — Fresh groceries, delivered')">
    <section class="bg-gradient-to-br from-brand-700 via-brand-700 to-brand-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
            <div class="max-w-2xl">
                <h1 class="text-3xl sm:text-4xl font-bold leading-tight">{{ __('Fresh groceries, delivered fast.') }}</h1>
                <p class="mt-3 text-brand-100 sm:text-lg">{{ __('Dals, rice, atta, spices, dry fruits and daily essentials — in English and తెలుగు.') }}</p>

                <form action="{{ route('search') }}" method="GET" role="search" class="mt-6 flex max-w-xl rounded-lg bg-white p-1 shadow-lg">
                    <input type="search" name="q" aria-label="{{ __('Search products') }}" placeholder="{{ __('Try “toor dal” or “కందిపప్పు”') }}"
                        class="w-full min-w-0 rounded-md border-0 px-3 py-2.5 text-gray-900 focus:ring-2 focus:ring-brand-500">
                    <button type="submit" class="shrink-0 rounded-md bg-sun-300 px-5 text-sm font-bold text-brand-900 hover:bg-sun-400">{{ __('Search') }}</button>
                </form>

                <ul class="mt-6 flex flex-wrap gap-x-6 gap-y-2 text-sm text-brand-100">
                    <li class="flex items-center gap-2"><span aria-hidden="true">✓</span> {{ __('Cash on delivery') }}</li>
                    <li class="flex items-center gap-2"><span aria-hidden="true">✓</span> {{ __('Free delivery on eligible orders') }}</li>
                    <li class="flex items-center gap-2"><span aria-hidden="true">✓</span> {{ __('Easy returns') }}</li>
                </ul>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        <h2 class="sr-only">{{ __('Shop sections') }}</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            @foreach(config('storefront.sections') as $sectionKey => $sectionItem)
                @php($tone = ['groceries' => 'bg-fresh-100 text-fresh-800', 'housekeeping' => 'bg-brand-100 text-brand-800', 'food' => 'bg-sun-200 text-sun-900', 'medicines' => 'bg-rose-100 text-rose-800'][$sectionKey])
                <a href="{{ route($sectionItem['route']) }}" wire:navigate class="group relative rounded-xl border border-gray-200 bg-white p-4 transition-shadow hover:shadow-md">
                    @unless($sectionItem['live'])
                        <span class="absolute right-3 top-3 rounded-full bg-sun-300 px-2 py-0.5 text-[10px] font-bold uppercase text-brand-900">{{ __('Soon') }}</span>
                    @endunless
                    <span class="flex h-11 w-11 items-center justify-center rounded-lg text-lg font-bold {{ $tone }}">{{ mb_substr(__($sectionItem['short']), 0, 1) }}</span>
                    <p class="mt-3 font-bold leading-tight text-gray-900 group-hover:text-brand-700">{{ __($sectionItem['label']) }}</p>
                    <p class="mt-1 text-xs text-gray-500 line-clamp-2">{{ __($sectionItem['blurb']) }}</p>
                </a>
            @endforeach
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">
        @if($categories->isNotEmpty())
            <section aria-labelledby="shop-by-category">
                <h2 id="shop-by-category" class="text-lg sm:text-xl font-bold text-gray-900 mb-4">{{ __('Shop by Category') }}</h2>
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-8 gap-3 sm:gap-4">
                    @foreach($categories as $category)
                        <a href="{{ route('categories.show', $category->slug) }}" wire:navigate class="group rounded-xl border border-gray-200 bg-white p-2 sm:p-3 text-center transition-shadow hover:shadow-md">
                            <div class="aspect-square overflow-hidden rounded-lg bg-brand-50">
                                @if($category->imageUrl())
                                    <img src="{{ $category->imageUrl() }}" alt="" loading="lazy" width="600" height="600" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                                @else
                                    <div class="flex h-full items-center justify-center text-2xl font-bold text-brand-700">{{ mb_substr($category->translated('name'), 0, 1) }}</div>
                                @endif
                            </div>
                            <p class="mt-2 text-xs sm:text-sm font-semibold leading-tight text-gray-800 group-hover:text-brand-800">{{ $category->translated('name') }}</p>
                            @if($category->alternateTranslated('name'))
                                <p class="text-[11px] leading-tight text-gray-500">{{ $category->alternateTranslated('name') }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @foreach([
            ['id' => 'featured', 'title' => __('Featured Products'), 'items' => $featured],
            ['id' => 'popular', 'title' => __('Popular Products'), 'items' => $bestsellers],
            ['id' => 'new-arrivals', 'title' => __('New Arrivals'), 'items' => $newArrivals],
        ] as $rail)
            @if($rail['items']->isNotEmpty())
                <section aria-labelledby="{{ $rail['id'] }}">
                    <div class="mb-4 flex items-baseline justify-between gap-4">
                        <h2 id="{{ $rail['id'] }}" class="text-lg sm:text-xl font-bold text-gray-900">{{ $rail['title'] }}</h2>
                        <a href="{{ route('search') }}" wire:navigate class="text-sm font-semibold text-brand-700 hover:text-brand-800">{{ __('View all') }} &rarr;</a>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                        @foreach($rail['items'] as $variant)
                            <x-product-card :variant="$variant" />
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    </div>
</x-layouts.storefront>
