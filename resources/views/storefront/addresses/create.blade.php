<x-layouts.storefront :title="__('Add Address').' — GroceryHub'">
    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Add Address') }}</h1>
        <form action="{{ route('addresses.store') }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 space-y-4">
            @csrf
            @include('storefront.addresses._form', ['address' => null])
            <button type="submit" class="w-full bg-fresh-600 hover:bg-fresh-700 text-white font-semibold py-2.5 rounded-md">{{ __('Save Address') }}</button>
        </form>
    </div>
</x-layouts.storefront>
