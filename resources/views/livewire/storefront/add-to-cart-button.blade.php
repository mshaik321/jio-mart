<div>
    @if($quantity > 0)
        <div class="flex items-center justify-between rounded-md bg-fresh-600 text-white shadow-sm">
            <button type="button" wire:click="decrement" wire:loading.attr="disabled"
                class="h-9 w-10 rounded-l-md text-lg font-bold hover:bg-fresh-700 disabled:opacity-60"
                aria-label="{{ __('Decrease quantity') }}">&minus;</button>
            <span class="text-sm font-semibold" aria-live="polite">{{ $quantity }}<span class="sr-only"> {{ __('in cart') }}</span></span>
            <button type="button" wire:click="add" wire:loading.attr="disabled"
                class="h-9 w-10 rounded-r-md text-lg font-bold hover:bg-fresh-700 disabled:opacity-60"
                aria-label="{{ __('Increase quantity') }}">+</button>
        </div>
    @else
        <button type="button" wire:click="add" wire:loading.attr="disabled"
            class="h-9 w-full rounded-md border border-fresh-600 bg-white text-sm font-semibold text-fresh-700 hover:bg-fresh-600 hover:text-white disabled:opacity-60">
            {{ __('Add to Cart') }}
        </button>
    @endif

    @if($error)
        <p class="mt-1 text-xs text-red-600" role="alert">{{ $error }}</p>
    @endif
</div>
