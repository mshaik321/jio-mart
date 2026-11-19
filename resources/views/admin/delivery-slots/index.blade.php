<x-layouts.admin title="Delivery Slots">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Delivery Slots</h2>
        <a href="{{ route('admin.delivery-slots.create') }}" class="bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2 rounded-md">+ New Slot</a>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">Name</th><th class="px-4 py-2">Time</th><th class="px-4 py-2">Max Orders</th><th class="px-4 py-2">Active</th><th class="px-4 py-2"></th></tr>
            </thead>
            <tbody>
                @foreach($slots as $slot)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $slot->name }}</td>
                        <td class="px-4 py-2">{{ $slot->start_time }} - {{ $slot->end_time }}</td>
                        <td class="px-4 py-2">{{ $slot->max_orders }}</td>
                        <td class="px-4 py-2">{{ $slot->is_active ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <a href="{{ route('admin.delivery-slots.edit', $slot) }}" class="text-emerald-700 hover:underline">Edit</a>
                            <form action="{{ route('admin.delivery-slots.destroy', $slot) }}" method="POST" class="inline" onsubmit="return confirm('Delete this slot?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $slots->links() }}</div>
</x-layouts.admin>
