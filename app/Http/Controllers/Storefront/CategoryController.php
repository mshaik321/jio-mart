<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductVariant;

class CategoryController extends Controller
{
    public function show(Category $category)
    {
        $categoryIds = $category->children()->pluck('id')->push($category->id);

        $query = ProductVariant::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->whereHas('product', function ($q) use ($categoryIds) {
                $q->where('status', ProductStatus::Active)->whereIn('category_id', $categoryIds);
            })
            ->with(['product.primaryImage', 'product.brand']);

        $query = app(\App\Support\ProductFilters::class)->apply($query, request());

        $variants = $query->paginate(24)->withQueryString();

        $brands = \App\Models\Brand::query()
            ->whereHas('products', fn ($q) => $q->whereIn('category_id', $categoryIds))
            ->orderBy('name')
            ->get();

        return view('storefront.categories.show', compact('category', 'variants', 'brands'));
    }
}
