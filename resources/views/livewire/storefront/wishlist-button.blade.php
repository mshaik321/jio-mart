<div>
    <button wire:click="toggle" type="button"
        class="h-10 w-12 flex items-center justify-center rounded-md border {{ $inWishlist ? 'border-rose-400 bg-rose-50 text-rose-600' : 'border-gray-300 text-gray-500 hover:border-rose-300' }}"
        title="{{ $inWishlist ? __('Remove from wishlist') : __('Add to wishlist') }}" aria-label="{{ $inWishlist ? __('Remove from wishlist') : __('Add to wishlist') }}">
        {{ $inWishlist ? '♥' : '♡' }}
    </button>
</div>
