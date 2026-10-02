<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Models\DeliverySlot;
use App\Models\DeliveryZone;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function store(Request $request, CheckoutService $checkout, CartService $carts)
    {
        $data = $request->validate([
            'address_id' => ['required', 'exists:addresses,id'],
            'delivery_slot_id' => ['nullable', 'exists:delivery_slots,id'],
            'payment_method' => ['required', 'in:cod,online'],
        ]);

        $customer = $request->user()->customer;
        $address = Address::query()->where('customer_id', $customer->id)->find($data['address_id']);

        if (! $address) {
            return response()->json(['message' => 'Address not found.'], 404);
        }

        $zone = DeliveryZone::findServicing($address->pincode);

        if (! $zone) {
            return response()->json(['message' => 'We do not deliver to this pincode yet.'], 422);
        }

        $slot = isset($data['delivery_slot_id']) ? DeliverySlot::find($data['delivery_slot_id']) : null;
        $cart = $carts->getOrCreateCart($customer, 'api:'.$request->user()->id)->load('items.variant.product', 'coupon');

        try {
            $order = $checkout->placeOrder($customer, $cart, $address, $zone, $slot, PaymentMethod::from($data['payment_method']));
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new OrderResource($order->load('items'));
    }
}
