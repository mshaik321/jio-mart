@php($old = fn ($field, $default = '') => old($field, $address->{$field} ?? $default))

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Label') }}</label>
    <select name="label" class="w-full rounded-md border-gray-300 text-sm">
        @foreach(['Home', 'Work', 'Other'] as $label)
            <option value="{{ $label }}" @selected($old('label', 'Home') === $label)>{{ __($label) }}</option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Recipient Name') }}</label>
    <input type="text" name="recipient_name" value="{{ $old('recipient_name') }}" class="w-full rounded-md border-gray-300 text-sm" required>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone') }}</label>
    <input type="text" name="phone" value="{{ $old('phone') }}" class="w-full rounded-md border-gray-300 text-sm" required>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address Line 1') }}</label>
    <input type="text" name="line1" value="{{ $old('line1') }}" class="w-full rounded-md border-gray-300 text-sm" required>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address Line 2') }}</label>
    <input type="text" name="line2" value="{{ $old('line2') }}" class="w-full rounded-md border-gray-300 text-sm">
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('City') }}</label>
        <input type="text" name="city" value="{{ $old('city') }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('State') }}</label>
        <input type="text" name="state" value="{{ $old('state') }}" class="w-full rounded-md border-gray-300 text-sm" required>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Pincode') }}</label>
    <input type="text" name="pincode" value="{{ $old('pincode') }}" class="w-full rounded-md border-gray-300 text-sm" required>
</div>

<label class="flex items-center gap-2 text-sm text-gray-700">
    <input type="checkbox" name="is_default" value="1" @checked($old('is_default', false)) class="rounded border-gray-300 text-emerald-700">
    {{ __('Set as default address') }}
</label>

@if($errors->any())
    <div class="text-sm text-red-600">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
