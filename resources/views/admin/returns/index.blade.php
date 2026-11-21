<x-layouts.admin title="Returns">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Returns</h2>
    <div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">Return #</th><th class="px-4 py-2">Order</th><th class="px-4 py-2">Customer</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th></tr>
            </thead>
            <tbody>
                @foreach($returns as $return)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $return->return_number }}</td>
                        <td class="px-4 py-2">{{ $return->order->order_number }}</td>
                        <td class="px-4 py-2">{{ $return->customer->user->name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ ucfirst($return->status->value) }}</td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('admin.returns.show', $return) }}" class="text-brand-700 hover:underline">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $returns->links() }}</div>
</x-layouts.admin>
