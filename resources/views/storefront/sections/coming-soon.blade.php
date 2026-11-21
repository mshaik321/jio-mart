<x-layouts.storefront :title="__($section['label']).' — GroceryHub'" :section="$sectionKey">
    <section class="bg-gradient-to-br from-brand-700 via-brand-700 to-brand-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 text-center">
            <span class="inline-block rounded-full bg-sun-300 px-3 py-1 text-xs font-bold uppercase tracking-wider text-brand-900">{{ __('Coming soon') }}</span>
            <h1 class="mt-4 text-3xl sm:text-4xl font-bold leading-tight">{{ __($section['label']) }}</h1>
            <p class="mt-3 mx-auto max-w-xl text-brand-100 sm:text-lg">{{ __($section['blurb']) }}</p>
        </div>
    </section>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
        <p class="text-lg font-semibold text-gray-900">{{ __('We are getting this ready for you.') }}</p>
        <p class="mt-2 text-gray-600">{{ __('In the meantime, our groceries and provisions store is open — fresh stock, delivered to your door.') }}</p>
        <a href="{{ route('home') }}" wire:navigate
            class="mt-6 inline-flex items-center rounded-md bg-fresh-600 px-6 py-3 font-semibold text-white hover:bg-fresh-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
            {{ __('Shop Groceries') }} &rarr;
        </a>
    </div>
</x-layouts.storefront>
