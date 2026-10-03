<?php

namespace App\Http\Resources;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'payment_method' => $this->payment_method->value,
            'payment_status' => $this->payment_status->value,
            'subtotal' => Money::format($this->subtotal),
            'discount_amount' => Money::format($this->discount_amount),
            'tax_amount' => Money::format($this->tax_amount),
            'delivery_charge' => Money::format($this->delivery_charge),
            'grand_total' => Money::format($this->grand_total),
            'placed_at' => $this->placed_at?->toIso8601String(),
            'shipping_address' => [
                'recipient_name' => $this->shipping_recipient_name,
                'phone' => $this->shipping_phone,
                'line1' => $this->shipping_line1,
                'line2' => $this->shipping_line2,
                'city' => $this->shipping_city,
                'state' => $this->shipping_state,
                'pincode' => $this->shipping_pincode,
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
