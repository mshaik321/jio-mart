<?php

namespace App\Http\Controllers\Storefront;

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
            ->with('translations')
            ->orderBy('sort_order')
            ->take(16)
            ->get();

        $rail = fn (string $flag) => ProductVariant::query()
            ->listable()
            ->whereHas('product', fn ($q) => $q->where($flag, true))
            ->inCatalogOrder()
            ->take(10)
            ->get();

        $featured = $rail('is_featured');
        $bestsellers = $rail('is_bestseller');
        $newArrivals = $rail('is_new_arrival');

        return view('storefront.home', compact('categories', 'featured', 'bestsellers', 'newArrivals'));
    }
}
