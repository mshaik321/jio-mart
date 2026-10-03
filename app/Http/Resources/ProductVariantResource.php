<?php

namespace App\Http\Resources;

use App\Support\Money;
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
            'mrp' => Money::format($this->mrp),
            'selling_price' => Money::format($this->selling_price),
            'is_default' => $this->is_default,
            'in_stock' => $this->totalStock() > 0,
            'stock_quantity' => $this->totalStock(),
        ];
    }
}
