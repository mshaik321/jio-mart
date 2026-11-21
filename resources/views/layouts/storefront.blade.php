<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'GroceryHub') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|noto-sans-telugu:400,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <div class="min-h-screen flex flex-col">
            <header class="bg-brand-700 text-white sticky top-0 z-40 shadow">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center h-16 gap-3 sm:gap-4">
                        <a href="{{ route('home') }}" wire:navigate.hover class="flex items-center gap-2 text-xl font-bold tracking-tight shrink-0">
                            <x-application-logo class="h-8 w-8 fill-current text-sun-300" />
                            <span>Grocery<span class="text-sun-300">Hub</span></span>
                        </a>

                        <form action="{{ route('search') }}" method="GET" role="search" class="hidden md:flex flex-1 max-w-xl">
                            <input
                                type="search"
                                name="q"
                                value="{{ request('q') }}"
                                aria-label="{{ __('Search products') }}"
                                placeholder="{{ __('Search for atta, rice, dal, oil...') }}"
                                class="w-full rounded-l-md border-0 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-brand-500"
                            >
                            <button type="submit" class="bg-sun-300 hover:bg-sun-400 text-brand-900 rounded-r-md px-4 text-sm font-bold">
                                {{ __('Search') }}
                            </button>
                        </form>

                        <nav class="ml-auto flex items-center gap-3 sm:gap-4 text-sm font-medium shrink-0" aria-label="{{ __('Account') }}">
                            <x-language-switcher tone="light" class="hidden sm:flex" />

                            <a href="{{ route('wishlist.index') }}" wire:navigate class="flex items-center gap-1 hover:text-sun-300" title="{{ __('Wishlist') }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                                <span class="hidden lg:inline">{{ __('Wishlist') }}</span>
                            </a>
                            <a href="{{ route('cart.index') }}" wire:navigate class="flex items-center gap-1 hover:text-sun-300" title="{{ __('Cart') }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                <span class="hidden lg:inline">{{ __('Cart') }}</span>
                                <livewire:storefront.cart-badge />
                            </a>
                            @auth
                                @if(auth()->user()->isStaff())
                                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center rounded-md bg-sun-300 text-brand-900 p-1.5 font-semibold hover:bg-sun-400 sm:px-2.5 sm:py-1" title="{{ __('Admin') }}">
                                        <svg class="h-5 w-5 sm:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                                        <span class="sr-only sm:not-sr-only">{{ __('Admin') }}</span>
                                    </a>
                                @endif
                                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-1 hover:text-sun-300" title="{{ __('My Account') }}">
                                    <svg class="h-5 w-5 sm:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                    <span class="sr-only sm:not-sr-only">{{ __('My Account') }}</span>
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                                    @csrf
                                    <button type="submit" class="hover:text-sun-300">{{ __('Log out') }}</button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" wire:navigate class="hover:text-sun-300">{{ __('Log in') }}</a>
                                <a href="{{ route('register') }}" wire:navigate class="hidden sm:inline-flex rounded-md bg-sun-300 px-3 py-1.5 font-bold text-brand-900 hover:bg-sun-400">{{ __('Sign up') }}</a>
                            @endauth
                        </nav>
                    </div>

                    <div class="flex items-center gap-2 pb-3 md:hidden">
                        <form action="{{ route('search') }}" method="GET" role="search" class="flex flex-1">
                            <input
                                type="search"
                                name="q"
                                value="{{ request('q') }}"
                                aria-label="{{ __('Search products') }}"
                                placeholder="{{ __('Search products') }}"
                                class="w-full min-w-0 rounded-l-md border-0 px-3 py-2 text-sm text-gray-900 focus:ring-2 focus:ring-brand-500"
                            >
                            <button type="submit" class="bg-sun-300 text-brand-900 rounded-r-md px-3 text-sm font-bold">{{ __('Search') }}</button>
                        </form>
                        <x-language-switcher tone="light" class="sm:hidden" />
                    </div>
                </div>

                @php($activeSection = $section ?? 'groceries')

            <nav class="bg-brand-800" aria-label="{{ __('Shop sections') }}">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex gap-1 overflow-x-auto whitespace-nowrap py-2 text-sm [scrollbar-width:none]">
                    @foreach(config('storefront.sections') as $sectionKey => $sectionItem)
                        <a href="{{ route($sectionItem['route']) }}" wire:navigate
                            @if($activeSection === $sectionKey) aria-current="page" @endif
                            class="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 font-semibold {{ $activeSection === $sectionKey ? 'bg-sun-300 text-brand-900' : 'text-white hover:bg-brand-700' }}">
                            {{ __($sectionItem['label']) }}
                            @unless($sectionItem['live'])
                                <span class="rounded-full bg-fresh-500 px-1.5 py-0.5 text-[10px] font-bold uppercase leading-none text-white">{{ __('Soon') }}</span>
                            @endunless
                        </a>
                    @endforeach
                </div>
            </nav>

            @if($activeSection === 'groceries' && $navCategories->isNotEmpty())
                <nav class="bg-white border-b border-gray-200" aria-label="{{ __('Categories') }}">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex gap-1 overflow-x-auto whitespace-nowrap py-2 text-sm [scrollbar-width:none]">
                        @foreach($navCategories as $navCategory)
                            <a href="{{ route('categories.show', $navCategory->slug) }}" wire:navigate
                                class="rounded-full px-3 py-1 {{ request()->route('category')?->is($navCategory) ? 'bg-sun-300 text-brand-900 font-semibold' : 'text-gray-700 hover:bg-sun-100' }}">
                                {{ $navCategory->translated('name') }}
                            </a>
                        @endforeach
                    </div>
                </nav>
            @endif
            </header>

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="bg-brand-900 text-brand-100 mt-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4 text-sm">
                    <div class="lg:col-span-2">
                        <p class="font-semibold text-white text-lg mb-2">GroceryHub</p>
                        <p>{{ __('Fresh groceries, delivered to your doorstep.') }}</p>
                        <p class="mt-4 text-gray-500">&copy; {{ now()->year }} GroceryHub</p>
                    </div>
                    <div>
                        <p class="font-semibold text-white mb-2">{{ __('Shop') }}</p>
                        <ul class="space-y-1 mb-4">
                            @foreach(config('storefront.sections') as $footerSection)
                                <li><a href="{{ route($footerSection['route']) }}" wire:navigate class="hover:text-sun-300">{{ __($footerSection['label']) }}</a></li>
                            @endforeach
                        </ul>
                        <ul class="space-y-1">
                            @foreach($navCategories->take(6) as $footerCategory)
                                <li><a href="{{ route('categories.show', $footerCategory->slug) }}" wire:navigate class="hover:text-white">{{ $footerCategory->translated('name') }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <p class="font-semibold text-white mb-2">{{ __('Language') }}</p>
                        <x-language-switcher tone="light" />
                    </div>
                </div>
            </footer>
        </div>

        @livewireScripts
    </body>
</html>
