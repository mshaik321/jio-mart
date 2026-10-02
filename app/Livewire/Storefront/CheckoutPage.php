<?php

namespace App\Livewire\Storefront;

use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Models\DeliverySlot;
use App\Models\DeliveryZone;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Support\CartContext;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CheckoutPage extends Component
{
    public ?int $addressId = null;

    public ?int $deliverySlotId = null;

    public string $paymentMethod = 'cod';

    public ?string $error = null;

    public function mount(): void
    {
        $customer = CartContext::customer();
        $this->addressId = $customer?->defaultAddress?->id ?? $customer?->addresses()->first()?->id;
    }

    private function matchedZone(?Address $address): ?DeliveryZone
    {
        if (! $address) {
            return null;
        }

        return DeliveryZone::query()->where('is_active', true)->get()
            ->first(fn (DeliveryZone $zone) => $zone->servicesPincode($address->pincode));
    }

    public function placeOrder(CheckoutService $checkout, CartService $carts)
    {
        $this->error = null;
        $customer = CartContext::customer();
        $address = Address::query()->where('customer_id', $customer->id)->find($this->addressId);

        if (! $address) {
            $this->error = 'Please select a delivery address.';

            return null;
        }

        $zone = $this->matchedZone($address);

        if (! $zone) {
            $this->error = 'Sorry, we do not deliver to this pincode yet.';

            return null;
        }

        $slot = $this->deliverySlotId ? DeliverySlot::find($this->deliverySlotId) : null;
        $cart = $carts->getOrCreateCart($customer, CartContext::sessionId());

        try {
            $order = $checkout->placeOrder(
                $customer, $cart, $address, $zone, $slot, PaymentMethod::from($this->paymentMethod),
            );

            $this->dispatch('cart-updated');

            return $this->redirect(route('orders.confirmation', $order->order_number), navigate: false);
        } catch (ValidationException $e) {
            $this->error = $e->getMessage();

            return null;
        }
    }

    public function render()
    {
        $customer = CartContext::customer();
        $addresses = $customer->addresses()->get();
        $slots = DeliverySlot::query()->where('is_active', true)->get();

        $cart = app(CartService::class)
            ->getOrCreateCart($customer, CartContext::sessionId())
            ->load('items.variant.product', 'coupon');

        $totals = app(CartService::class)->totals($cart);

        $address = $addresses->firstWhere('id', $this->addressId);
        $zone = $this->matchedZone($address);
        $deliveryCharge = 0.0;

        if ($zone) {
            $deliveryCharge = (float) $zone->delivery_charge;

            if ($zone->free_delivery_above !== null && $totals['subtotal'] >= (float) $zone->free_delivery_above) {
                $deliveryCharge = 0.0;
            }
        }

        $grandTotal = round($totals['subtotal'] + $totals['tax_amount'] + $deliveryCharge - $totals['discount'], 2);

        return view('livewire.storefront.checkout-page', compact(
            'addresses', 'slots', 'cart', 'totals', 'zone', 'deliveryCharge', 'grandTotal',
        ));
    }
}
