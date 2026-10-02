<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_variant_id' => $this->product_variant_id,
            'product_name' => $this->variant->product->name,
            'variant_name' => $this->variant->variant_name,
            'image_url' => $this->variant->product->primaryImage?->url(),
            'quantity' => $this->quantity,
            'price' => (float) $this->price_snapshot,
            'line_total' => (float) $this->price_snapshot * $this->quantity,
        ];
    }
}
