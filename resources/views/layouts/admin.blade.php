<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Admin' }} — GroceryHub</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-100 text-gray-900">
        <div class="min-h-screen flex">
            <aside class="w-64 bg-gray-900 text-gray-200 flex-shrink-0 hidden lg:flex lg:flex-col">
                <div class="h-16 flex items-center px-5 text-white font-bold text-lg border-b border-gray-800">
                    GroceryHub Admin
                </div>
                <nav class="flex-1 overflow-y-auto py-4 text-sm">
                    @php
                        $sections = [
                            'Overview' => [['admin.dashboard', 'Dashboard']],
                            'Catalog' => [
                                ['admin.categories.index', 'Categories'],
                                ['admin.brands.index', 'Brands'],
                                ['admin.products.index', 'Products'],
                            ],
                            'Inventory' => [
                                ['admin.inventory.index', 'Stock'],
                                ['admin.suppliers.index', 'Suppliers'],
                                ['admin.purchase-orders.index', 'Purchase Orders'],
                            ],
                            'Sales' => [
                                ['admin.orders.index', 'Orders'],
                                ['admin.coupons.index', 'Coupons'],
                                ['admin.returns.index', 'Returns'],
                                ['admin.reviews.index', 'Reviews'],
                            ],
                            'Delivery' => [
                                ['admin.delivery-zones.index', 'Delivery Zones'],
                                ['admin.delivery-slots.index', 'Delivery Slots'],
                                ['admin.delivery-agents.index', 'Delivery Agents'],
                            ],
                            'Reports' => [
                                ['admin.reports.index', 'Reports'],
                            ],
                            'Administration' => [
                                ['admin.users.index', 'Users'],
                                ['admin.roles.index', 'Roles & Permissions'],
                                ['admin.settings.index', 'Settings'],
                                ['admin.audit-logs.index', 'Audit Logs'],
                            ],
                        ];
                    @endphp

                    @foreach($sections as $heading => $links)
                        <p class="px-5 mt-4 mb-1 text-xs uppercase tracking-wider text-gray-500">{{ $heading }}</p>
                        @foreach($links as [$routeName, $label])
                            @if(Route::has($routeName))
                                <a href="{{ route($routeName) }}"
                                   class="block px-5 py-2 {{ request()->routeIs($routeName.'*') ? 'bg-emerald-700 text-white' : 'hover:bg-gray-800' }}">
                                    {{ $label }}
                                </a>
                            @endif
                        @endforeach
                    @endforeach
                </nav>
                <div class="border-t border-gray-800 p-4 text-xs">
                    <p class="text-gray-400">{{ auth()->user()->name }}</p>
                    <div class="flex justify-between mt-2">
                        <a href="{{ route('home') }}" class="text-emerald-400 hover:underline">View Store</a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-white">Log Out</button>
                        </form>
                    </div>
                </div>
            </aside>

            <div class="flex-1 min-w-0">
                <header class="bg-white border-b border-gray-200 h-16 flex items-center px-6">
                    <h1 class="text-lg font-semibold text-gray-900">{{ $title ?? 'Dashboard' }}</h1>
                </header>

                <main class="p-6">
                    @if(session('status'))
                        <div class="bg-emerald-50 text-emerald-800 text-sm rounded-md p-3 mb-4">{{ session('status') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="bg-red-50 text-red-700 text-sm rounded-md p-3 mb-4">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
        @livewireScripts
    </body>
</html>
