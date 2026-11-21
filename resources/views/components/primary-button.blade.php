<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-brand-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-brand-800 focus:bg-brand-800 active:bg-brand-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-60 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
