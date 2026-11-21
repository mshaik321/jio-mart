@props(['divider' => null])

{{--
    Shown whenever Google sign-in is configured. Outside production it is also
    shown unconfigured, so the flow is visible in development; the redirect
    route then explains that Google sign-in isn't available.
--}}
@if (\App\Services\GoogleAuthService::isConfigured() || ! app()->isProduction())
    <a href="{{ route('auth.google.redirect') }}"
        class="w-full inline-flex items-center justify-center gap-3 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-800">
        <svg class="h-5 w-5 shrink-0" viewBox="0 0 48 48" aria-hidden="true">
            <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
            <path fill="#FF3D00" d="m6.306 14.691 6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
            <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
            <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
        </svg>
        <span>{{ __('Continue with Google') }}</span>
    </a>

    <div class="my-6 flex items-center gap-3 text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
        <span class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></span>
        {{ $divider ?? __('or use your email') }}
        <span class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></span>
    </div>
@endif
