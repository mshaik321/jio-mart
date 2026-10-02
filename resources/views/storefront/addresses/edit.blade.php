<x-layouts.storefront :title="'Edit Address — GroceryHub'">
    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Address</h1>
        <form action="{{ route('addresses.update', $address) }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 space-y-4">
            @csrf @method('PUT')
            @include('storefront.addresses._form')
            <button type="submit" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-2.5 rounded-md">Update Address</button>
        </form>
    </div>
</x-layouts.storefront>
