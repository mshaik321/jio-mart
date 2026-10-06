@php($old = fn ($field, $default = '') => old($field, $category->{$field} ?? $default))

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
    <input type="text" name="name" value="{{ $old('name') }}" class="w-full rounded-md border-gray-300 text-sm" required>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
    <select name="parent_id" class="w-full rounded-md border-gray-300 text-sm">
        <option value="">— None (top-level) —</option>
        @foreach($parents as $parent)
            <option value="{{ $parent->id }}" @selected((string) $old('parent_id') === (string) $parent->id)>{{ $parent->name }}</option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
    <textarea name="description" rows="3" class="w-full rounded-md border-gray-300 text-sm">{{ $old('description') }}</textarea>
</div>

@include('admin.partials._translations', [
    'translatable' => $category ?? null,
    'fields' => [
        'name' => ['Name', 'text'],
        'description' => ['Description', 'textarea'],
    ],
])

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
    <input type="number" name="sort_order" value="{{ $old('sort_order', 0) }}" class="w-full rounded-md border-gray-300 text-sm">
</div>

<label class="flex items-center gap-2 text-sm text-gray-700">
    <input type="checkbox" name="is_active" value="1" @checked($old('is_active', true)) class="rounded border-gray-300 text-emerald-700">
    Active
</label>
