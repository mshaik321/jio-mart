<x-layouts.admin title="Coupons">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Coupons</h2>
        @can('coupons.create')
            <a href="{{ route('admin.coupons.create') }}" class="bg-fresh-600 hover:bg-fresh-700 text-white text-sm font-semibold px-4 py-2 rounded-md">+ New Coupon</a>
        @endcan
    </div>
    <div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-2">Code</th><th class="px-4 py-2">Type</th><th class="px-4 py-2">Value</th><th class="px-4 py-2">Used</th><th class="px-4 py-2">Active</th><th class="px-4 py-2"></th></tr>
            </thead>
            <tbody>
                @foreach($coupons as $coupon)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $coupon->code }}</td>
                        <td class="px-4 py-2">{{ ucfirst($coupon->type->value) }}</td>
                        <td class="px-4 py-2">{{ $coupon->type->value === 'percentage' ? $coupon->value.'%' : '₹'.number_format((float) $coupon->value, 2) }}</td>
                        <td class="px-4 py-2">{{ $coupon->times_used }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                        <td class="px-4 py-2">{{ $coupon->is_active ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-2 text-right space-x-2">
                            @can('coupons.update')
                                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-brand-700 hover:underline">Edit</a>
                            @endcan
                            @can('coupons.delete')
                                <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline" onsubmit="return confirm('Delete this coupon?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $coupons->links() }}</div>
</x-layouts.admin>
