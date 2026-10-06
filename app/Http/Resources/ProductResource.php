<?php

namespace App\Http\Resources;

use App\Support\Money;
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
            'localized_name' => $this->whenLoaded('translations', fn () => $this->translated('name')),
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'translations' => $this->whenLoaded('translations', fn () => $this->translations->mapWithKeys(fn ($translation) => [$translation->locale => [
                'name' => $translation->name,
                'short_description' => $translation->short_description,
                'description' => $translation->description,
            ]])),
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
            'mrp' => Money::format($this->mrp),
            'selling_price' => Money::format($this->selling_price),
            'tax_rate' => Money::format($this->tax_rate),
            'is_featured' => $this->is_featured,
            'is_bestseller' => $this->is_bestseller,
            'is_new_arrival' => $this->is_new_arrival,
            'image_url' => $this->whenLoaded('primaryImage', fn () => $this->primaryImage?->url()),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
