<?php

namespace App\Livewire\Storefront;

use App\Models\WishlistItem;
use App\Services\CartService;
use App\Support\CartContext;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.storefront')]
class WishlistPage extends Component
{
    public ?string $error = null;

    public function remove(int $itemId): void
    {
        $customer = CartContext::customer();
        WishlistItem::query()->where('customer_id', $customer->id)->where('id', $itemId)->delete();
    }

    public function moveToCart(int $itemId, CartService $carts): void
    {
        $this->error = null;
        $customer = CartContext::customer();
        $item = WishlistItem::query()->where('customer_id', $customer->id)->where('id', $itemId)->first();

        if (! $item) {
            return;
        }

        try {
            $cart = $carts->getOrCreateCart($customer, CartContext::sessionId());
            $carts->addItem($cart, $item->variant, 1);
            $item->delete();
            $this->dispatch('cart-updated');
        } catch (ValidationException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function render()
    {
        $customer = CartContext::customer();

        $items = $customer
            ? WishlistItem::query()->where('customer_id', $customer->id)->with(['variant.product.primaryImage', 'variant.product.translations'])->latest()->get()
            : collect();

        return view('livewire.storefront.wishlist-page', compact('items'));
    }
}
