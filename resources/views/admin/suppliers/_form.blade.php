@php($old = fn ($field, $default = '') => old($field, $supplier->{$field} ?? $default))

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input type="text" name="name" value="{{ $old('name') }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
        <input type="text" name="contact_person" value="{{ $old('contact_person') }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
        <input type="text" name="phone" value="{{ $old('phone') }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" value="{{ $old('email') }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
    <textarea name="address" rows="2" class="w-full rounded-md border-gray-300 text-sm">{{ $old('address') }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">GSTIN</label>
    <input type="text" name="gstin" value="{{ $old('gstin') }}" class="w-full rounded-md border-gray-300 text-sm">
</div>
<label class="flex items-center gap-2 text-sm text-gray-700">
    <input type="checkbox" name="is_active" value="1" @checked($old('is_active', true)) class="rounded border-gray-300 text-brand-700"> Active
</label>
