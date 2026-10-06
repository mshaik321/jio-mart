<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\ProductVariant;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function getOrCreateCart(?Customer $customer, string $sessionId): Cart
    {
        if ($customer) {
            $this->mergeGuestCart($customer, $sessionId);

            return Cart::query()->firstOrCreate(['customer_id' => $customer->id]);
        }

        return Cart::query()->firstOrCreate(['session_id' => $sessionId, 'customer_id' => null]);
    }

    /**
     * Move a guest cart into the customer's cart. Runs from the Login listener
     * (see AppServiceProvider), so it must never throw: a line that can no
     * longer be sold at the combined quantity is dropped.
     */
    public function mergeGuestCart(Customer $customer, string $sessionId): void
    {
        $guest = Cart::query()
            ->where('session_id', $sessionId)
            ->whereNull('customer_id')
            ->first();

        if (! $guest) {
            return;
        }

        $cart = Cart::query()->firstOrCreate(['customer_id' => $customer->id]);

        foreach ($guest->items as $item) {
            if (! $item->variant) {
                continue;
            }

            try {
                $this->addItem($cart, $item->variant, $item->quantity);
            } catch (ValidationException) {
                // Sold out or unavailable since it was added as a guest.
            }
        }

        $guest->delete();
    }

    public function addItem(Cart $cart, ProductVariant $variant, int $quantity): void
    {
        $existing = $cart->items()->where('product_variant_id', $variant->id)->first();
        $newQuantity = $quantity + ($existing?->quantity ?? 0);

        $this->assertSellable($variant, $newQuantity);

        $cart->items()->updateOrCreate(
            ['product_variant_id' => $variant->id],
            ['quantity' => $newQuantity, 'price_snapshot' => $variant->selling_price],
        );
    }

    public function updateQuantity(Cart $cart, ProductVariant $variant, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($cart, $variant);

            return;
        }

        $this->assertSellable($variant, $quantity);

        $cart->items()->where('product_variant_id', $variant->id)->update([
            'quantity' => $quantity,
            'price_snapshot' => $variant->selling_price,
        ]);
    }

    public function removeItem(Cart $cart, ProductVariant $variant): void
    {
        $cart->items()->where('product_variant_id', $variant->id)->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->update(['coupon_id' => null]);
    }

    /**
     * @return array{subtotal: float, tax_amount: float, discount: float}
     */
    public function totals(Cart $cart): array
    {
        $cart->loadMissing('items.variant.product', 'coupon');

        $subtotal = (float) $cart->items->sum(fn ($i) => $i->price_snapshot * $i->quantity);

        $taxAmount = (float) $cart->items->sum(function ($i) {
            $rate = (float) $i->variant->product->tax_rate;

            return ($i->price_snapshot * $i->quantity) * $rate / 100;
        });

        $discount = 0.0;

        if ($cart->coupon && $cart->coupon->isCurrentlyValid()) {
            $discount = app(CouponService::class)->calculateDiscount($cart->coupon, $subtotal);
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'discount' => $discount,
        ];
    }

    private function assertSellable(ProductVariant $variant, int $quantity): void
    {
        $product = $variant->product;

        if (! $variant->is_active || $product->status !== ProductStatus::Active) {
            throw ValidationException::withMessages(['variant' => __('This product is currently unavailable.')]);
        }

        if ($variant->totalStock() < $quantity) {
            throw ValidationException::withMessages(['variant' => __('Not enough stock available for this item.')]);
        }

        if ($quantity < $product->min_order_qty || $quantity > $product->max_order_qty) {
            throw ValidationException::withMessages([
                'variant' => __('Quantity must be between :min and :max.', ['min' => $product->min_order_qty, 'max' => $product->max_order_qty]),
            ]);
        }
    }
}
