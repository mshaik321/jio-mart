<?php

namespace App\Support;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

/**
 * Resolves "whose cart is this" for both guest (session-scoped) and
 * authenticated customers, without ever creating a cart as a side effect.
 */
class CartContext
{
    public static function customer(): ?Customer
    {
        $user = Auth::user();

        return $user?->customer;
    }

    public static function sessionId(): string
    {
        return session()->getId();
    }
}
