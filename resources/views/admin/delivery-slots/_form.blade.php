@php($old = fn ($field, $default = '') => old($field, $slot->{$field} ?? $default))

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
    <input type="text" name="name" value="{{ $old('name') }}" class="w-full rounded-md border-gray-300 text-sm" required>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
        <input type="time" name="start_time" value="{{ $old('start_time') }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
        <input type="time" name="end_time" value="{{ $old('end_time') }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Max Orders</label>
    <input type="number" name="max_orders" value="{{ $old('max_orders', 100) }}" class="w-full rounded-md border-gray-300 text-sm" required>
</div>
<label class="flex items-center gap-2 text-sm text-gray-700">
    <input type="checkbox" name="is_active" value="1" @checked($old('is_active', true)) class="rounded border-gray-300 text-emerald-700"> Active
</label>
