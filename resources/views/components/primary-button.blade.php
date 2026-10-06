<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-emerald-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-emerald-800 focus:bg-emerald-800 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-60 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
