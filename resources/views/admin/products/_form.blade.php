@php($old = fn ($field, $default = '') => old($field, $product->{$field} ?? $default))

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
        <input type="text" name="sku" value="{{ $old('sku') }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input type="text" name="name" value="{{ $old('name') }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
</div>

<div class="grid grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
        <select name="category_id" class="w-full rounded-md border-gray-300 text-sm" required>
            <option value="">Select</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) $old('category_id') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
        <select name="brand_id" class="w-full rounded-md border-gray-300 text-sm">
            <option value="">None</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}" @selected((string) $old('brand_id') === (string) $brand->id)>{{ $brand->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
        <select name="unit_id" class="w-full rounded-md border-gray-300 text-sm" required>
            <option value="">Select</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}" @selected((string) $old('unit_id') === (string) $unit->id)>{{ $unit->name }} ({{ $unit->short_code }})</option>
            @endforeach
        </select>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
    <input type="text" name="short_description" value="{{ $old('short_description') }}" class="w-full rounded-md border-gray-300 text-sm">
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
    <textarea name="description" rows="4" class="w-full rounded-md border-gray-300 text-sm">{{ $old('description') }}</textarea>
</div>

<div class="grid grid-cols-4 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">MRP</label>
        <input type="number" step="0.01" name="mrp" value="{{ $old('mrp') }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price</label>
        <input type="number" step="0.01" name="selling_price" value="{{ $old('selling_price') }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tax Rate (%)</label>
        <input type="number" step="0.01" name="tax_rate" value="{{ $old('tax_rate', 0) }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
        <input type="number" step="0.001" name="weight" value="{{ $old('weight') }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
</div>

<div class="grid grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Min Order Qty</label>
        <input type="number" name="min_order_qty" value="{{ $old('min_order_qty', 1) }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Max Order Qty</label>
        <input type="number" name="max_order_qty" value="{{ $old('max_order_qty', 50) }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select name="status" class="w-full rounded-md border-gray-300 text-sm">
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" @selected($old('status', 'draft') === $status->value)>{{ ucfirst($status->value) }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="flex gap-6">
    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="is_featured" value="1" @checked($old('is_featured', false)) class="rounded border-gray-300 text-emerald-700"> Featured
    </label>
    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="is_bestseller" value="1" @checked($old('is_bestseller', false)) class="rounded border-gray-300 text-emerald-700"> Bestseller
    </label>
    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="is_new_arrival" value="1" @checked($old('is_new_arrival', false)) class="rounded border-gray-300 text-emerald-700"> New Arrival
    </label>
</div>
