<x-layouts.admin title="Edit Delivery Zone">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Edit Delivery Zone</h2>
    <form action="{{ route('admin.delivery-zones.update', $zone) }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 space-y-4 max-w-lg">
        @csrf @method('PUT')
        @include('admin.delivery-zones._form')
        <button type="submit" class="bg-fresh-600 hover:bg-fresh-700 text-white font-semibold px-4 py-2 rounded-md text-sm">Update Zone</button>
    </form>
</x-layouts.admin>
