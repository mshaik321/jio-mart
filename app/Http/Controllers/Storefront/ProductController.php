<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(Product $product, Request $request)
    {
        $product->load(['category', 'brand', 'unit', 'images', 'variants' => fn ($q) => $q->where('is_active', true)]);

        $selectedVariant = $request->integer('variant')
            ? $product->variants->firstWhere('id', $request->integer('variant'))
            : null;

        $variant = $selectedVariant ?? $product->variants->firstWhere('is_default', true) ?? $product->variants->first();

        $reviews = $product->reviews()
            ->where('status', ReviewStatus::Approved)
            ->with('customer.user')
            ->latest()
            ->paginate(10);

        $averageRating = round((float) $product->approvedReviews()->avg('rating'), 1);

        return view('storefront.products.show', compact('product', 'variant', 'reviews', 'averageRating'));
    }
}
