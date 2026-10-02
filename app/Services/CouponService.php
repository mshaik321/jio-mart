<?php

namespace App\Services;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\Customer;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function validateForCustomer(Coupon $coupon, Customer $customer, float $subtotal): void
    {
        if (! $coupon->isCurrentlyValid()) {
            throw ValidationException::withMessages(['coupon' => 'This coupon is no longer valid.']);
        }

        if ($subtotal < (float) $coupon->min_order_amount) {
            throw ValidationException::withMessages([
                'coupon' => "Minimum order amount of {$coupon->min_order_amount} is required for this coupon.",
            ]);
        }

        if ($coupon->per_customer_limit !== null) {
            $used = $coupon->usages()->where('customer_id', $customer->id)->count();

            if ($used >= $coupon->per_customer_limit) {
                throw ValidationException::withMessages(['coupon' => 'You have already used this coupon.']);
            }
        }
    }

    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        $discount = $coupon->type === CouponType::Percentage
            ? $subtotal * ((float) $coupon->value / 100)
            : (float) $coupon->value;

        if ($coupon->max_discount_amount !== null) {
            $discount = min($discount, (float) $coupon->max_discount_amount);
        }

        return round(min($discount, $subtotal), 2);
    }
}
