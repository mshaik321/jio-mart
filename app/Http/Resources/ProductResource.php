<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'brand' => $this->whenLoaded('brand', fn () => $this->brand ? [
                'id' => $this->brand->id,
                'name' => $this->brand->name,
            ] : null),
            'unit' => $this->whenLoaded('unit', fn () => [
                'name' => $this->unit->name,
                'short_code' => $this->unit->short_code,
            ]),
            'mrp' => (float) $this->mrp,
            'selling_price' => (float) $this->selling_price,
            'tax_rate' => (float) $this->tax_rate,
            'is_featured' => $this->is_featured,
            'is_bestseller' => $this->is_bestseller,
            'is_new_arrival' => $this->is_new_arrival,
            'image_url' => $this->whenLoaded('primaryImage', fn () => $this->primaryImage?->url()),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
