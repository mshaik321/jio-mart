@php($old = fn ($field, $default = '') => old($field, $coupon->{$field} ?? $default))

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
        <input type="text" name="code" value="{{ $old('code') }}" class="w-full rounded-md border-gray-300 text-sm uppercase" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
        <select name="type" class="w-full rounded-md border-gray-300 text-sm">
            <option value="percentage" @selected($old('type', 'percentage') === 'percentage')>Percentage</option>
            <option value="fixed" @selected($old('type') === 'fixed')>Fixed Amount</option>
        </select>
    </div>
</div>

<div class="grid grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Value</label>
        <input type="number" step="0.01" name="value" value="{{ $old('value') }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Min Order Amount</label>
        <input type="number" step="0.01" name="min_order_amount" value="{{ $old('min_order_amount', 0) }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Max Discount</label>
        <input type="number" step="0.01" name="max_discount_amount" value="{{ $old('max_discount_amount') }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Starts At</label>
        <input type="datetime-local" name="starts_at" value="{{ $old('starts_at') }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Ends At</label>
        <input type="datetime-local" name="ends_at" value="{{ $old('ends_at') }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Total Usage Limit</label>
        <input type="number" name="usage_limit" value="{{ $old('usage_limit') }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Per-Customer Limit</label>
        <input type="number" name="per_customer_limit" value="{{ $old('per_customer_limit', 1) }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
</div>

<label class="flex items-center gap-2 text-sm text-gray-700">
    <input type="checkbox" name="is_active" value="1" @checked($old('is_active', true)) class="rounded border-gray-300 text-brand-700"> Active
</label>
