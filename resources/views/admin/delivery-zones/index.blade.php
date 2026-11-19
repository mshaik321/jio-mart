<x-layouts.admin title="Delivery Zones">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Delivery Zones</h2>
        <a href="{{ route('admin.delivery-zones.create') }}" class="bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2 rounded-md">+ New Zone</a>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">Name</th><th class="px-4 py-2">Pincodes</th><th class="px-4 py-2">Charge</th><th class="px-4 py-2">Free Above</th><th class="px-4 py-2"></th></tr>
            </thead>
            <tbody>
                @foreach($zones as $zone)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $zone->name }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ implode(', ', $zone->pincodes ?? []) }}</td>
                        <td class="px-4 py-2">₹{{ number_format((float) $zone->delivery_charge, 2) }}</td>
                        <td class="px-4 py-2">{{ $zone->free_delivery_above ? '₹'.number_format((float) $zone->free_delivery_above, 2) : '—' }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <a href="{{ route('admin.delivery-zones.edit', $zone) }}" class="text-emerald-700 hover:underline">Edit</a>
                            <form action="{{ route('admin.delivery-zones.destroy', $zone) }}" method="POST" class="inline" onsubmit="return confirm('Delete this zone?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $zones->links() }}</div>
</x-layouts.admin>
