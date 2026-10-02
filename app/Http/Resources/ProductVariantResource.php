<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'variant_name' => $this->variant_name,
            'attributes' => $this->attributes,
            'mrp' => (float) $this->mrp,
            'selling_price' => (float) $this->selling_price,
            'is_default' => $this->is_default,
            'in_stock' => $this->totalStock() > 0,
            'stock_quantity' => $this->totalStock(),
        ];
    }
}
