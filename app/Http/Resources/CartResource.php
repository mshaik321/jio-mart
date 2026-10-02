<?php

namespace App\Http\Resources;

use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totals = app(CartService::class)->totals($this->resource);

        return [
            'id' => $this->id,
            'items' => CartItemResource::collection($this->items),
            'coupon_code' => $this->coupon?->code,
            'subtotal' => $totals['subtotal'],
            'tax_amount' => $totals['tax_amount'],
            'discount' => $totals['discount'],
        ];
    }
}
