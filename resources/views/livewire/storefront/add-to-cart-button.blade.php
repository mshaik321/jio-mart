<div>
    @if($error)
        <p class="text-xs text-red-600 mb-1">{{ $error }}</p>
    @endif

    @if($added)
        <button type="button" wire:click="$set('added', false)" class="w-full bg-emerald-50 text-emerald-700 border border-emerald-300 text-sm font-semibold py-2 rounded-md">
            Added &check;
        </button>
    @else
        <button wire:click="add" wire:loading.attr="disabled" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold py-2 rounded-md disabled:opacity-60">
            Add to Cart
        </button>
    @endif
</div>
