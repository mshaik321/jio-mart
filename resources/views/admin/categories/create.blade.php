<x-layouts.admin title="New Category">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">New Category</h2>
    <form action="{{ route('admin.categories.store') }}" method="POST" class="bg-white border border-gray-200 rounded-lg p-5 space-y-4 max-w-lg">
        @csrf
        @include('admin.categories._form', ['category' => null])
        <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-semibold px-4 py-2 rounded-md text-sm">Create Category</button>
    </form>
</x-layouts.admin>
