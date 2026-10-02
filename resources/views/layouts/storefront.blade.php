<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'GroceryHub') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <div class="min-h-screen flex flex-col">
            <header class="bg-emerald-700 text-white sticky top-0 z-40 shadow">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16 gap-4">
                        <a href="{{ route('home') }}" wire:navigate.hover class="text-xl font-bold tracking-tight shrink-0">
                            Grocery<span class="text-emerald-200">Hub</span>
                        </a>

                        <form action="{{ route('search') }}" method="GET" class="hidden sm:flex flex-1 max-w-xl">
                            <input
                                type="text"
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="Search for atta, rice, oil, masala..."
                                class="w-full rounded-l-md border-0 px-4 py-2 text-gray-900 focus:ring-2 focus:ring-emerald-400"
                            >
                            <button type="submit" class="bg-emerald-900 hover:bg-emerald-800 rounded-r-md px-4 text-sm font-semibold">
                                Search
                            </button>
                        </form>

                        <nav class="flex items-center gap-4 text-sm font-medium shrink-0">
                            <a href="{{ route('wishlist.index') }}" wire:navigate class="hover:text-emerald-200">Wishlist</a>
                            <a href="{{ route('cart.index') }}" wire:navigate class="hover:text-emerald-200 flex items-center gap-1">
                                Cart
                                <livewire:storefront.cart-badge />
                            </a>
                            @auth
                                <a href="{{ route('dashboard') }}" wire:navigate class="hover:text-emerald-200">My Account</a>
                            @else
                                <a href="{{ route('login') }}" wire:navigate class="hover:text-emerald-200">Login</a>
                            @endauth
                        </nav>
                    </div>
                </div>
            </header>

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="bg-gray-900 text-gray-300 mt-12">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 text-sm">
                    <p class="font-semibold text-white text-lg mb-2">GroceryHub</p>
                    <p>Fresh groceries, delivered to your doorstep. &copy; {{ now()->year }} GroceryHub.</p>
                </div>
            </footer>
        </div>

        @livewireScripts
    </body>
</html>
