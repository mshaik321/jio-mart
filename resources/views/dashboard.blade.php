<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="mb-4">{{ __('Welcome back, :name!', ['name' => auth()->user()->name]) }}</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <a href="{{ route('orders.index') }}" wire:navigate class="block border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <p class="font-semibold">{{ __('My Orders') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Track and manage your orders') }}</p>
                        </a>
                        <a href="{{ route('addresses.index') }}" wire:navigate class="block border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <p class="font-semibold">{{ __('My Addresses') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Manage delivery addresses') }}</p>
                        </a>
                        <a href="{{ route('profile') }}" wire:navigate class="block border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <p class="font-semibold">{{ __('Profile') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Update your account details') }}</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
