<x-layouts.admin title="Edit Delivery Agent">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Edit Delivery Agent</h2>
    <form action="{{ route('admin.delivery-agents.update', $agent) }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 space-y-4 max-w-lg">
        @csrf @method('PUT')
        @include('admin.delivery-agents._form')
        <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-semibold px-4 py-2 rounded-md text-sm">Update Agent</button>
    </form>
</x-layouts.admin>
