<x-layouts.admin title="New Supplier">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">New Supplier</h2>
    <form action="{{ route('admin.suppliers.store') }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 space-y-4 max-w-lg">
        @csrf
        @include('admin.suppliers._form', ['supplier' => null])
        <button type="submit" class="bg-fresh-600 hover:bg-fresh-700 text-white font-semibold px-4 py-2 rounded-md text-sm">Create Supplier</button>
    </form>
</x-layouts.admin>
