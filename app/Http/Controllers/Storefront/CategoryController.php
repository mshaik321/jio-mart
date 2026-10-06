<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Support\ProductFilters;

class CategoryController extends Controller
{
    public function show(Category $category)
    {
        abort_unless($category->is_active, 404);

        $category->load('translations');

        $categoryIds = $category->children()->where('is_active', true)->pluck('id')->push($category->id);

        $query = ProductVariant::query()
            ->listable()
            ->whereHas('product', fn ($q) => $q->whereIn('category_id', $categoryIds))
            ->with('product.brand');

        $query = app(ProductFilters::class)->apply($query, request());

        $variants = $query->paginate(24)->withQueryString();

        $brands = Brand::query()
            ->whereHas('products', fn ($q) => $q->whereIn('category_id', $categoryIds))
            ->orderBy('name')
            ->get();

        return view('storefront.categories.show', compact('category', 'variants', 'brands'));
    }
}
