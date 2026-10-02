<?php

namespace App\Livewire\Storefront;

use App\Models\Cart;
use App\Support\CartContext;
use Livewire\Attributes\On;
use Livewire\Component;

class CartBadge extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    #[On('cart-updated')]
    public function refreshCount(): void
    {
        $customer = CartContext::customer();
        $sessionId = CartContext::sessionId();

        $cart = $customer
            ? Cart::query()->where('customer_id', $customer->id)->first()
            : Cart::query()->where('session_id', $sessionId)->whereNull('customer_id')->first();

        $this->count = (int) ($cart?->items()->sum('quantity') ?? 0);
    }

    public function render()
    {
        return view('livewire.storefront.cart-badge');
    }
}
