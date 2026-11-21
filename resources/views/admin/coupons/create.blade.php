<x-layouts.admin title="New Coupon">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">New Coupon</h2>
    <form action="{{ route('admin.coupons.store') }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 space-y-4 max-w-lg">
        @csrf
        @include('admin.coupons._form', ['coupon' => null])
        <button type="submit" class="bg-fresh-600 hover:bg-fresh-700 text-white font-semibold px-4 py-2 rounded-md text-sm">Create Coupon</button>
    </form>
</x-layouts.admin>
