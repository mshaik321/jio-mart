@php($old = fn ($field, $default = '') => old($field, $agent->{$field} ?? $default))

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
    <input type="text" name="name" value="{{ $old('name') }}" class="w-full rounded-md border-gray-300 text-sm" required>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
    <input type="text" name="phone" value="{{ $old('phone') }}" class="w-full rounded-md border-gray-300 text-sm" required>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Type</label>
        <input type="text" name="vehicle_type" value="{{ $old('vehicle_type') }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Number</label>
        <input type="text" name="vehicle_number" value="{{ $old('vehicle_number') }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
</div>
<label class="flex items-center gap-2 text-sm text-gray-700">
    <input type="checkbox" name="is_active" value="1" @checked($old('is_active', true)) class="rounded border-gray-300 text-brand-700"> Active
</label>
