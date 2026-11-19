<x-layouts.admin title="Delivery Agents">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Delivery Agents</h2>
        <a href="{{ route('admin.delivery-agents.create') }}" class="bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-4 py-2 rounded-md">+ New Agent</a>
    </div>
    <div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">Name</th><th class="px-4 py-2">Phone</th><th class="px-4 py-2">Vehicle</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th></tr>
            </thead>
            <tbody>
                @foreach($agents as $agent)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $agent->name }}</td>
                        <td class="px-4 py-2">{{ $agent->phone }}</td>
                        <td class="px-4 py-2">{{ $agent->vehicle_type }} {{ $agent->vehicle_number }}</td>
                        <td class="px-4 py-2">{{ ucfirst($agent->status->value) }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            <a href="{{ route('admin.delivery-agents.edit', $agent) }}" class="text-emerald-700 hover:underline">Edit</a>
                            <form action="{{ route('admin.delivery-agents.destroy', $agent) }}" method="POST" class="inline" onsubmit="return confirm('Remove this agent?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $agents->links() }}</div>
</x-layouts.admin>
