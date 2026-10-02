<?php

namespace App\Livewire\Storefront;

use App\Models\ProductVariant;
use App\Services\CartService;
use App\Support\CartContext;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class AddToCartButton extends Component
{
    public ProductVariant $variant;

    public int $quantity = 1;

    public bool $added = false;

    public ?string $error = null;

    public function mount(ProductVariant $variant): void
    {
        $this->variant = $variant;
    }

    public function add(CartService $carts): void
    {
        $this->error = null;

        try {
            $cart = $carts->getOrCreateCart(CartContext::customer(), CartContext::sessionId());
            $carts->addItem($cart, $this->variant, $this->quantity);
            $this->added = true;
            $this->dispatch('cart-updated');
        } catch (ValidationException $e) {
            $this->error = $e->getMessage();
        }
    }

    #[On('cart-updated')]
    public function refreshCount(): void
    {
        // Allows sibling components (e.g. the header cart badge) to react; no local state needed here.
    }

    public function render()
    {
        return view('livewire.storefront.add-to-cart-button');
    }
}
