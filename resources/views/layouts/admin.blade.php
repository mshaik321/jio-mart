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
        <div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="min-h-screen flex">
            {{-- Below lg the sidebar is an off-canvas drawer opened from the header's menu button. --}}
            <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"
                 class="fixed inset-0 z-40 bg-gray-900/60 lg:hidden" aria-hidden="true"></div>

            <aside id="admin-sidebar"
                   :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': ! sidebarOpen }"
                   class="fixed inset-y-0 left-0 z-50 w-64 -translate-x-full bg-gray-900 text-gray-200 flex flex-col transition-transform duration-200 ease-in-out lg:static lg:z-auto lg:translate-x-0 lg:flex-shrink-0">
                <div class="h-16 flex items-center justify-between gap-2 px-5 text-white font-bold text-lg border-b border-gray-800">
                    GroceryHub Admin
                    <button type="button" @click="sidebarOpen = false" class="lg:hidden -mr-2 rounded-md p-2 text-gray-400 hover:bg-gray-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-brand-500" aria-label="Close menu">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <nav class="flex-1 overflow-y-auto py-4 text-sm">
                    @php
                        $sections = [
                            'Overview' => [['admin.dashboard', 'Dashboard']],
                            'Catalog' => [
                                ['admin.categories.index', 'Categories', 'categories.view'],
                                ['admin.brands.index', 'Brands', 'brands.view'],
                                ['admin.products.index', 'Products', 'products.view'],
                            ],
                            'Inventory' => [
                                ['admin.inventory.index', 'Stock', 'inventory.view'],
                                ['admin.suppliers.index', 'Suppliers', 'suppliers.view'],
                                ['admin.purchase-orders.index', 'Purchase Orders', 'purchases.view'],
                            ],
                            'Sales' => [
                                ['admin.orders.index', 'Orders', 'orders.view'],
                                ['admin.coupons.index', 'Coupons', 'coupons.view'],
                                ['admin.returns.index', 'Returns', 'returns.view'],
                                ['admin.reviews.index', 'Reviews', 'reviews.view'],
                            ],
                            'Delivery' => [
                                ['admin.delivery-zones.index', 'Delivery Zones', 'delivery.manage'],
                                ['admin.delivery-slots.index', 'Delivery Slots', 'delivery.manage'],
                                ['admin.delivery-agents.index', 'Delivery Agents', 'delivery.manage'],
                            ],
                            'Reports' => [
                                ['admin.reports.index', 'Reports', 'reports.view'],
                            ],
                            'Administration' => [
                                ['admin.users.index', 'Users', 'users.view'],
                                ['admin.roles.index', 'Roles & Permissions', 'roles.manage'],
                                ['admin.settings.index', 'Settings', 'settings.manage'],
                                ['admin.audit-logs.index', 'Audit Logs', 'audit_logs.view'],
                            ],
                        ];
                    @endphp

                    @foreach($sections as $heading => $links)
                        @php
                            $visibleLinks = collect($links)->filter(fn ($link) => Route::has($link[0]) && (! isset($link[2]) || auth()->user()->can($link[2])));
                        @endphp
                        @continue($visibleLinks->isEmpty())
                        <p class="px-5 mt-4 mb-1 text-xs uppercase tracking-wider text-gray-400">{{ $heading }}</p>
                        @foreach($visibleLinks as $link)
                            @php([$routeName, $label] = $link)
                            @if(true)
                                <a href="{{ route($routeName) }}"
                                   class="block px-5 py-2 {{ request()->routeIs($routeName.'*') ? 'bg-brand-700 text-white' : 'hover:bg-gray-800' }}">
                                    {{ $label }}
                                </a>
                            @endif
                        @endforeach
                    @endforeach
                </nav>
                <div class="border-t border-gray-800 p-4 text-xs">
                    <p class="text-gray-400">{{ auth()->user()->name }}</p>
                    <div class="flex justify-between mt-2">
                        <a href="{{ route('home') }}" class="text-sun-400 hover:underline">View Store</a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-white">Log Out</button>
                        </form>
                    </div>
                </div>
            </aside>

            <div class="flex-1 min-w-0">
                <header class="bg-white border-b border-gray-200 h-16 flex items-center gap-3 px-4 sm:px-6">
                    <button type="button" @click="sidebarOpen = true" :aria-expanded="sidebarOpen.toString()" aria-controls="admin-sidebar"
                            class="lg:hidden -ml-2 rounded-md p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500" aria-label="Open menu">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-900 truncate">{{ $title ?? 'Dashboard' }}</h1>
                </header>

                <main class="p-4 sm:p-6">
                    @if(session('status'))
                        <div class="bg-fresh-50 text-fresh-800 text-sm rounded-md p-3 mb-4">{{ session('status') }}</div>
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
        <script>
            // Admin forms use visual <label> blocks without for/id; link each to the control that follows it.
            document.addEventListener('DOMContentLoaded', () => {
                let n = 0;
                document.querySelectorAll('main label:not([for])').forEach((label) => {
                    if (label.querySelector('input, select, textarea')) return;
                    const control = label.parentElement && label.parentElement.querySelector('input:not([type=hidden]), select, textarea');
                    if (!control) return;
                    if (!control.id) control.id = 'field-' + (++n);
                    label.setAttribute('for', control.id);
                });
            });
        </script>
        @livewireScripts
    </body>
</html>
