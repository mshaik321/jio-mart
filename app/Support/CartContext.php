<?php

namespace App\Support;

use App\Models\CartItem;
use App\Models\Customer;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;

/**
 * Resolves "whose cart is this" for both guest (session-scoped) and
 * authenticated customers, without ever creating a cart as a side effect.
 */
class CartContext
{
    public static function customer(): ?Customer
    {
        return Auth::user()?->customerProfile();
    }

    /**
     * The key a guest cart is stored under. It lives in the session *data*
     * rather than being the session ID itself, because Laravel regenerates the
     * session ID on login (before the Login event fires); keying by the raw ID
     * would orphan the guest's cart at exactly the moment it should be merged.
     * For a session that predates this token, the first value is the current
     * session ID, so existing guest carts keep resolving.
     */
    public static function sessionId(): string
    {
        $session = session();

        if (! $session->has('cart_token')) {
            $session->put('cart_token', $session->getId());
        }

        return $session->get('cart_token');
    }

    /**
     * How many of this variant are already in the visitor's cart (0 if none, or no cart yet).
     */
    public static function quantityOf(ProductVariant $variant): int
    {
        $customer = self::customer();

        return (int) CartItem::query()
            ->where('product_variant_id', $variant->id)
            ->whereHas('cart', fn ($cart) => $customer
                ? $cart->where('customer_id', $customer->id)
                : $cart->where('session_id', self::sessionId())->whereNull('customer_id'))
            ->value('quantity');
    }
}
