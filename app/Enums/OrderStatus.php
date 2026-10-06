<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Packed = 'packed';
    case ReadyForDelivery = 'ready_for_delivery';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Returned = 'returned';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Confirmed => __('Confirmed'),
            self::Processing => __('Processing'),
            self::Packed => __('Packed'),
            self::ReadyForDelivery => __('Ready for Delivery'),
            self::OutForDelivery => __('Out for Delivery'),
            self::Delivered => __('Delivered'),
            self::Cancelled => __('Cancelled'),
            self::Returned => __('Returned'),
            self::Refunded => __('Refunded'),
        };
    }

    public function isCancellable(): bool
    {
        return in_array($this, [self::Pending, self::Confirmed, self::Processing], true);
    }
}
