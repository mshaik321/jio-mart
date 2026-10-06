<?php

namespace App\Livewire\Storefront;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\CouponService;
use App\Support\CartContext;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.storefront')]
class CartPage extends Component
{
    public string $couponCode = '';

    public ?string $error = null;

    private function cart(): Cart
    {
        return app(CartService::class)
            ->getOrCreateCart(CartContext::customer(), CartContext::sessionId())
            ->load('items.variant.product.primaryImage', 'items.variant.product.translations', 'coupon');
    }

    public function updateQuantity(int $variantId, int $quantity, CartService $carts): void
    {
        $this->error = null;
        $variant = ProductVariant::findOrFail($variantId);

        try {
            $carts->updateQuantity($this->cart(), $variant, $quantity);
            $this->dispatch('cart-updated');
        } catch (ValidationException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function removeItem(int $variantId, CartService $carts): void
    {
        $variant = ProductVariant::findOrFail($variantId);
        $carts->removeItem($this->cart(), $variant);
        $this->dispatch('cart-updated');
    }

    public function applyCoupon(CouponService $coupons): void
    {
        $this->error = null;
        $cart = $this->cart();

        $coupon = Coupon::query()->where('code', strtoupper(trim($this->couponCode)))->first();

        if (! $coupon) {
            $this->error = __('Invalid coupon code.');

            return;
        }

        $customer = CartContext::customer();

        if (! $customer) {
            $this->error = __('Please log in to apply a coupon.');

            return;
        }

        try {
            $totals = app(CartService::class)->totals($cart);
            $coupons->validateForCustomer($coupon, $customer, $totals['subtotal']);
            $cart->update(['coupon_id' => $coupon->id]);
            $this->couponCode = '';
        } catch (ValidationException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function removeCoupon(): void
    {
        $this->cart()->update(['coupon_id' => null]);
    }

    public function render()
    {
        $cart = $this->cart();
        $totals = app(CartService::class)->totals($cart);

        return view('livewire.storefront.cart-page', [
            'cart' => $cart,
            'totals' => $totals,
        ]);
    }
}
