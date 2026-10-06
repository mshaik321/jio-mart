<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'localized_name' => $this->whenLoaded('translations', fn () => $this->translated('name')),
            'translations' => $this->whenLoaded('translations', fn () => $this->translations->mapWithKeys(fn ($translation) => [$translation->locale => [
                'name' => $translation->name,
                'description' => $translation->description,
            ]])),
            'slug' => $this->slug,
            'parent_id' => $this->parent_id,
            'image' => $this->image,
            'image_url' => $this->imageUrl(),
        ];
    }
}
