<x-layouts.admin title="New Delivery Slot">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">New Delivery Slot</h2>
    <form action="{{ route('admin.delivery-slots.store') }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 space-y-4 max-w-lg">
        @csrf
        @include('admin.delivery-slots._form', ['slot' => null])
        <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-semibold px-4 py-2 rounded-md text-sm">Create Slot</button>
    </form>
</x-layouts.admin>
