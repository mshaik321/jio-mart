<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductVariant;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        $baseQuery = fn () => ProductVariant::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->whereHas('product', fn ($q) => $q->where('status', ProductStatus::Active))
            ->with(['product.primaryImage', 'product.category']);

        $featured = $baseQuery()->whereHas('product', fn ($q) => $q->where('is_featured', true))->take(8)->get();
        $bestsellers = $baseQuery()->whereHas('product', fn ($q) => $q->where('is_bestseller', true))->take(8)->get();
        $newArrivals = $baseQuery()->whereHas('product', fn ($q) => $q->where('is_new_arrival', true))->take(8)->get();

        return view('storefront.home', compact('categories', 'featured', 'bestsellers', 'newArrivals'));
    }
}
