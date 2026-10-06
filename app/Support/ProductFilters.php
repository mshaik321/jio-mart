<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Shared brand/price/availability/sort filtering for the category and search
 * listing pages. MySQL LIKE/WHERE queries only for V1; architected so a
 * future Scout/Elasticsearch driver could replace the `whereHas` term match
 * without touching the controllers that call this.
 */
class ProductFilters
{
    public function apply(Builder $query, Request $request): Builder
    {
        if ($brandIds = $request->query('brands')) {
            $brandIds = is_array($brandIds) ? $brandIds : explode(',', (string) $brandIds);
            $query->whereHas('product', fn ($q) => $q->whereIn('brand_id', $brandIds));
        }

        if ($min = $request->query('min_price')) {
            $query->where('selling_price', '>=', (float) $min);
        }

        if ($max = $request->query('max_price')) {
            $query->where('selling_price', '<=', (float) $max);
        }

        if ($request->boolean('in_stock')) {
            $query->whereHas('inventories', fn ($q) => $q->whereColumn('quantity_on_hand', '>', 'quantity_reserved'));
        }

        match ($request->query('sort')) {
            'price_asc' => $query->orderBy('selling_price'),
            'price_desc' => $query->orderByDesc('selling_price'),
            'newest' => $query->latest('product_variants.created_at'),
            default => $query->inCatalogOrder(),
        };

        return $query;
    }
}
