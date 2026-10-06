{{--
    Per-locale catalog copy for every non-English supported locale (config/app.php
    supported_locales). Leave a locale's name blank to remove its translation.
    Expects: $translatable (model or null), $fields = [field => [label, type]].
--}}
@foreach(\App\Models\Product::translationLocales() as $locale)
    @php($translation = $translatable?->translation($locale))
    <fieldset class="border border-gray-200 rounded-md p-4 space-y-3" lang="{{ $locale }}">
        <legend class="px-1 text-sm font-semibold text-gray-700">{{ config("app.supported_locales.$locale") }} ({{ $locale }})</legend>
        @foreach($fields as $field => [$label, $type])
            <div>
                <label for="translations-{{ $locale }}-{{ $field }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
                @if($type === 'textarea')
                    <textarea id="translations-{{ $locale }}-{{ $field }}" name="translations[{{ $locale }}][{{ $field }}]" rows="3" class="w-full rounded-md border-gray-300 text-sm">{{ old("translations.$locale.$field", $translation?->{$field}) }}</textarea>
                @else
                    <input id="translations-{{ $locale }}-{{ $field }}" type="text" name="translations[{{ $locale }}][{{ $field }}]" value="{{ old("translations.$locale.$field", $translation?->{$field}) }}" class="w-full rounded-md border-gray-300 text-sm">
                @endif
                @error("translations.$locale.$field")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </fieldset>
@endforeach
