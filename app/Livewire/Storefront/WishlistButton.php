<?php

namespace App\Livewire\Storefront;

use App\Models\ProductVariant;
use App\Models\WishlistItem;
use App\Support\CartContext;
use Livewire\Component;

class WishlistButton extends Component
{
    public ProductVariant $variant;

    public bool $inWishlist = false;

    public function mount(ProductVariant $variant): void
    {
        $this->variant = $variant;
        $customer = CartContext::customer();

        $this->inWishlist = $customer
            ? WishlistItem::query()->where('customer_id', $customer->id)->where('product_variant_id', $variant->id)->exists()
            : false;
    }

    public function toggle(): void
    {
        $customer = CartContext::customer();

        if (! $customer) {
            $this->redirect(route('login'), navigate: false);

            return;
        }

        $existing = WishlistItem::query()
            ->where('customer_id', $customer->id)
            ->where('product_variant_id', $this->variant->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $this->inWishlist = false;
        } else {
            WishlistItem::create(['customer_id' => $customer->id, 'product_variant_id' => $this->variant->id]);
            $this->inWishlist = true;
        }
    }

    public function render()
    {
        return view('livewire.storefront.wishlist-button');
    }
}
