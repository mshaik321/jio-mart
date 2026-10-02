@php($old = fn ($field, $default = '') => old($field, $zone->{$field} ?? $default))

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
    <input type="text" name="name" value="{{ $old('name') }}" class="w-full rounded-md border-gray-300 text-sm" required>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Pincodes (comma-separated)</label>
    <input type="text" name="pincodes" value="{{ is_array($old('pincodes')) ? implode(', ', $old('pincodes')) : $old('pincodes') }}" class="w-full rounded-md border-gray-300 text-sm" required>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Charge</label>
        <input type="number" step="0.01" name="delivery_charge" value="{{ $old('delivery_charge', 0) }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Free Delivery Above</label>
        <input type="number" step="0.01" name="free_delivery_above" value="{{ $old('free_delivery_above') }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
</div>
<label class="flex items-center gap-2 text-sm text-gray-700">
    <input type="checkbox" name="is_active" value="1" @checked($old('is_active', true)) class="rounded border-gray-300 text-emerald-700"> Active
</label>
