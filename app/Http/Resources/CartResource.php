<?php

namespace App\Http\Resources;

use App\Services\CartService;
use App\Support\Money;
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
            'subtotal' => Money::format($totals['subtotal']),
            'tax_amount' => Money::format($totals['tax_amount']),
            'discount' => Money::format($totals['discount']),
        ];
    }
}
