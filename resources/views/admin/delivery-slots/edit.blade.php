<x-layouts.admin title="Edit Delivery Slot">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Edit Delivery Slot</h2>
    <form action="{{ route('admin.delivery-slots.update', $slot) }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 space-y-4 max-w-lg">
        @csrf @method('PUT')
        @include('admin.delivery-slots._form')
        <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-semibold px-4 py-2 rounded-md text-sm">Update Slot</button>
    </form>
</x-layouts.admin>
