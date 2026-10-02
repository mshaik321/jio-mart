<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request, CartService $carts)
    {
        $cart = $carts->getOrCreateCart($request->user()->customer, 'api:'.$request->user()->id)
            ->load('items.variant.product.primaryImage', 'coupon');

        return new CartResource($cart);
    }

    public function store(Request $request, CartService $carts)
    {
        $data = $request->validate([
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $carts->getOrCreateCart($request->user()->customer, 'api:'.$request->user()->id);
        $variant = ProductVariant::findOrFail($data['product_variant_id']);

        try {
            $carts->addItem($cart, $variant, $data['quantity']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new CartResource($cart->fresh('items.variant.product.primaryImage', 'coupon'));
    }

    public function update(Request $request, ProductVariant $variant, CartService $carts)
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0']]);

        $cart = $carts->getOrCreateCart($request->user()->customer, 'api:'.$request->user()->id);

        try {
            $carts->updateQuantity($cart, $variant, $data['quantity']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new CartResource($cart->fresh('items.variant.product.primaryImage', 'coupon'));
    }

    public function destroy(Request $request, ProductVariant $variant, CartService $carts)
    {
        $cart = $carts->getOrCreateCart($request->user()->customer, 'api:'.$request->user()->id);
        $carts->removeItem($cart, $variant);

        return new CartResource($cart->fresh('items.variant.product.primaryImage', 'coupon'));
    }
}
