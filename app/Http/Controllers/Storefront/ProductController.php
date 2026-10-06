<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\ProductStatus;
use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(Product $product, Request $request)
    {
        abort_unless($product->status === ProductStatus::Active, 404);

        $product->load([
            'category.translations', 'brand', 'unit', 'images', 'translations',
            'variants' => fn ($q) => $q->where('is_active', true)->with('inventories'),
        ]);

        $selectedVariant = $request->integer('variant')
            ? $product->variants->firstWhere('id', $request->integer('variant'))
            : null;

        $variant = $selectedVariant ?? $product->variants->firstWhere('is_default', true) ?? $product->variants->first();

        abort_if($variant === null, 404);

        $reviews = $product->reviews()
            ->where('status', ReviewStatus::Approved)
            ->with('customer.user')
            ->latest()
            ->paginate(10);

        $averageRating = round((float) $product->approvedReviews()->avg('rating'), 1);

        $related = ProductVariant::query()
            ->listable()
            ->whereHas('product', fn ($q) => $q->where('category_id', $product->category_id)->whereKeyNot($product->id))
            ->inCatalogOrder()
            ->take(5)
            ->get();

        return view('storefront.products.show', compact('product', 'variant', 'reviews', 'averageRating', 'related'));
    }
}
