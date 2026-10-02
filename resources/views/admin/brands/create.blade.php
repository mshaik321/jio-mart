<x-layouts.admin title="New Brand">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">New Brand</h2>
    <form action="{{ route('admin.brands.store') }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 space-y-4 max-w-lg">
        @csrf
        @include('admin.brands._form', ['brand' => null])
        <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-semibold px-4 py-2 rounded-md text-sm">Create Brand</button>
    </form>
</x-layouts.admin>
