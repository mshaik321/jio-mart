<?php

namespace App\Livewire\Storefront;

use App\Models\ProductVariant;
use App\Services\CartService;
use App\Support\CartContext;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * "Add to Cart" that turns into a − qty + stepper once the item is in the
 * cart. Every change re-reads the cart line first, so a card that went stale
 * (e.g. the quantity was changed on the cart page) still applies the right delta.
 */
class AddToCartButton extends Component
{
    public ProductVariant $variant;

    /** Quantity of this variant currently in the visitor's cart. */
    public int $quantity = 0;

    public ?string $error = null;

    public function mount(ProductVariant $variant): void
    {
        $this->variant = $variant;
        $this->quantity = CartContext::quantityOf($variant);
    }

    public function add(CartService $carts): void
    {
        $this->changeBy(1, $carts);
    }

    public function decrement(CartService $carts): void
    {
        $this->changeBy(-1, $carts);
    }

    private function changeBy(int $delta, CartService $carts): void
    {
        $this->error = null;

        $cart = $carts->getOrCreateCart(CartContext::customer(), CartContext::sessionId());
        $current = (int) $cart->items()->where('product_variant_id', $this->variant->id)->value('quantity');
        $target = $current + $delta;

        try {
            match (true) {
                $target <= 0 => $carts->removeItem($cart, $this->variant),
                $current === 0 => $carts->addItem($cart, $this->variant, $target),
                default => $carts->updateQuantity($cart, $this->variant, $target),
            };

            $this->quantity = max(0, $target);
            $this->dispatch('cart-updated');
        } catch (ValidationException $e) {
            $this->error = $e->getMessage();
            $this->quantity = $current;
        }
    }

    public function render()
    {
        return view('livewire.storefront.add-to-cart-button');
    }
}
