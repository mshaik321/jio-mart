@props(['tone' => 'dark'])

{{-- tone="light" for the green storefront header, "dark" for light backgrounds. --}}
<div {{ $attributes->merge(['class' => 'flex items-center gap-1 text-xs font-semibold']) }} role="group" aria-label="{{ __('Language') }}">
    @foreach (config('app.supported_locales') as $code => $label)
        @if (app()->getLocale() === $code)
            <span lang="{{ $code }}" aria-current="true"
                class="px-2 py-1 rounded-md {{ $tone === 'light' ? 'bg-white text-emerald-800' : 'bg-emerald-700 text-white' }}">{{ $label }}</span>
        @else
            <a href="{{ route('locale.update', $code) }}" lang="{{ $code }}" hreflang="{{ $code }}"
                class="px-2 py-1 rounded-md {{ $tone === 'light' ? 'text-emerald-50 hover:bg-emerald-600' : 'text-emerald-800 hover:bg-emerald-100 dark:text-emerald-300 dark:hover:bg-gray-700' }}">{{ $label }}</a>
        @endif
    @endforeach
</div>
