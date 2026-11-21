<x-layouts.storefront :title="__('My Addresses').' — GroceryHub'">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ __('My Addresses') }}</h1>
            <a href="{{ route('addresses.create') }}" class="bg-fresh-600 hover:bg-fresh-700 text-white text-sm font-semibold px-4 py-2 rounded-md">+ {{ __('Add Address') }}</a>
        </div>

        @if(session('status'))
            <div class="bg-fresh-50 text-fresh-800 text-sm rounded-md p-3 mb-4">{{ session('status') }}</div>
        @endif

        @if($addresses->isEmpty())
            <p class="text-gray-500">{{ __('You have no saved addresses yet.') }}</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($addresses as $address)
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <p class="font-semibold text-gray-900">{{ $address->label }}</p>
                            @if($address->is_default)
                                <span class="text-xs bg-brand-50 text-brand-700 px-2 py-0.5 rounded">{{ __('Default') }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-700 mt-1">{{ $address->recipient_name }} &middot; {{ $address->phone }}</p>
                        <p class="text-sm text-gray-600">{{ $address->line1 }}, {{ $address->line2 }}</p>
                        <p class="text-sm text-gray-600">{{ $address->city }}, {{ $address->state }} {{ $address->pincode }}</p>
                        <div class="flex gap-3 mt-3">
                            <a href="{{ route('addresses.edit', $address) }}" class="text-xs text-brand-700 underline">{{ __('Edit') }}</a>
                            <form action="{{ route('addresses.destroy', $address) }}" method="POST" onsubmit="return confirm(@js(__('Delete this address?')));">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 underline">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.storefront>
