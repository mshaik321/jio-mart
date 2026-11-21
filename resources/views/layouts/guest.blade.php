<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'GroceryHub') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|noto-sans-telugu:400,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-brand-50 dark:bg-gray-900">
        <div class="min-h-screen flex flex-col">
            <header class="w-full max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2 text-xl font-bold tracking-tight text-brand-800 dark:text-sun-300">
                    <x-application-logo class="h-8 w-8 fill-current text-brand-700 dark:text-sun-400" />
                    <span>Grocery<span class="text-fresh-600">Hub</span></span>
                </a>

                <x-language-switcher />
            </header>

            <main class="flex-1 flex items-start sm:items-center justify-center px-4 pb-10 sm:pb-16">
                <div class="w-full sm:max-w-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black/5 rounded-2xl px-5 py-7 sm:px-8 sm:py-8">
                    {{ $slot }}
                </div>
            </main>

            <footer class="pb-6 text-center text-xs text-gray-600 dark:text-gray-400">
                &copy; {{ now()->year }} GroceryHub &middot; {{ __('Fresh groceries, delivered to your doorstep.') }}
            </footer>
        </div>
    </body>
</html>
