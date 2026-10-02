<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\DeliverySlot;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private InventoryService $inventory,
        private CouponService $coupons,
    ) {}

    public function placeOrder(
        Customer $customer,
        Cart $cart,
        Address $address,
        DeliveryZone $zone,
        ?DeliverySlot $slot,
        PaymentMethod $method,
    ): Order {
        $cart->loadMissing('items.variant.product', 'coupon');

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        return DB::transaction(function () use ($customer, $cart, $address, $zone, $slot, $method) {
            $warehouse = Warehouse::query()->where('is_active', true)->firstOrFail();

            $subtotal = 0.0;
            $taxAmount = 0.0;
            $lines = [];

            foreach ($cart->items as $item) {
                $variant = $item->variant()->lockForUpdate()->first();
                $product = $variant->product;

                if (! $variant->is_active || $product->status !== ProductStatus::Active) {
                    throw ValidationException::withMessages(['cart' => "\"{$product->name}\" is no longer available."]);
                }

                if ($variant->totalStock() < $item->quantity) {
                    throw ValidationException::withMessages(['cart' => "Not enough stock for \"{$product->name}\"."]);
                }

                $unitPrice = (float) $variant->selling_price;
                $lineSubtotal = $unitPrice * $item->quantity;
                $lineTax = $lineSubtotal * ((float) $product->tax_rate / 100);

                $subtotal += $lineSubtotal;
                $taxAmount += $lineTax;

                $lines[] = [
                    'variant' => $variant,
                    'data' => [
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'product_name' => $product->name,
                        'sku' => $variant->sku,
                        'variant_name' => $variant->variant_name,
                        'unit_price' => $unitPrice,
                        'mrp' => $variant->mrp,
                        'tax_rate' => $product->tax_rate,
                        'tax_amount' => round($lineTax, 2),
                        'discount_amount' => 0,
                        'quantity' => $item->quantity,
                        'total' => round($lineSubtotal + $lineTax, 2),
                    ],
                ];
            }

            $discount = 0.0;
            $couponCode = null;

            if ($cart->coupon) {
                $this->coupons->validateForCustomer($cart->coupon, $customer, $subtotal);
                $discount = $this->coupons->calculateDiscount($cart->coupon, $subtotal);
                $couponCode = $cart->coupon->code;
            }

            $deliveryCharge = (float) $zone->delivery_charge;

            if ($zone->free_delivery_above !== null && $subtotal >= (float) $zone->free_delivery_above) {
                $deliveryCharge = 0.0;
            }

            $grandTotal = round($subtotal + $taxAmount + $deliveryCharge - $discount, 2);

            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customer->id,
                'coupon_id' => $cart->coupon_id,
                'coupon_code' => $couponCode,
                'address_id' => $address->id,
                'shipping_recipient_name' => $address->recipient_name,
                'shipping_phone' => $address->phone,
                'shipping_line1' => $address->line1,
                'shipping_line2' => $address->line2,
                'shipping_city' => $address->city,
                'shipping_state' => $address->state,
                'shipping_pincode' => $address->pincode,
                'subtotal' => round($subtotal, 2),
                'discount_amount' => $discount,
                'tax_amount' => round($taxAmount, 2),
                'delivery_charge' => $deliveryCharge,
                'grand_total' => $grandTotal,
                'payment_method' => $method,
                'payment_status' => OrderPaymentStatus::Pending,
                'status' => OrderStatus::Pending,
                'delivery_zone_id' => $zone->id,
                'delivery_slot_id' => $slot?->id,
                'placed_at' => now(),
            ]);

            foreach ($lines as $line) {
                $order->items()->create($line['data']);

                $this->inventory->recordMovement(
                    $warehouse,
                    $line['variant'],
                    InventoryMovementType::Sale,
                    -$line['data']['quantity'],
                    $order,
                    "Order {$order->order_number}",
                );
            }

            $order->statusHistories()->create([
                'status' => OrderStatus::Pending,
                'note' => 'Order placed.',
                'created_at' => now(),
            ]);

            $order->payments()->create([
                'method' => $method,
                'amount' => $grandTotal,
                'status' => PaymentStatus::Pending,
            ]);

            if ($cart->coupon) {
                $cart->coupon->usages()->create([
                    'customer_id' => $customer->id,
                    'order_id' => $order->id,
                    'discount_amount' => $discount,
                    'used_at' => now(),
                ]);
                $cart->coupon->increment('times_used');
            }

            $cart->items()->delete();
            $cart->update(['coupon_id' => null]);

            return $order->load('items', 'payments');
        });
    }

    private function generateOrderNumber(): string
    {
        return 'GH'.now()->format('Ymd').Str::upper(Str::random(6));
    }
}
