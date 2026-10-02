<x-layouts.admin title="Stock Movements">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">
        Stock Movements — {{ $variant->product->name }} ({{ $variant->variant_name }})
    </h2>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">Date</th><th class="px-4 py-2">Type</th><th class="px-4 py-2">Qty</th><th class="px-4 py-2">Balance After</th><th class="px-4 py-2">Warehouse</th><th class="px-4 py-2">Note</th></tr>
            </thead>
            <tbody>
                @foreach($movements as $movement)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 text-gray-500">{{ $movement->created_at->format('d M Y, h:i A') }}</td>
                        <td class="px-4 py-2">{{ $movement->type->value }}</td>
                        <td class="px-4 py-2 {{ $movement->quantity < 0 ? 'text-red-600' : 'text-emerald-700' }}">{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</td>
                        <td class="px-4 py-2">{{ $movement->balance_after }}</td>
                        <td class="px-4 py-2">{{ $movement->warehouse->name }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $movement->note }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $movements->links() }}</div>
</x-layouts.admin>
