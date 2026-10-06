<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\ProductVariant;
use App\Support\ProductFilters;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $query = ProductVariant::query()
            ->listable()
            ->when($term !== '', fn ($q) => $q->whereHas('product', fn ($product) => $product->matchingTerm($term)))
            ->with(['product.brand', 'product.category']);

        $query = app(ProductFilters::class)->apply($query, $request);

        $variants = $query->paginate(24)->withQueryString();

        $brands = Brand::query()
            ->where('is_active', true)
            ->whereHas('products', fn ($q) => $q->where('status', ProductStatus::Active))
            ->orderBy('name')
            ->get();

        return view('storefront.search.index', compact('variants', 'brands', 'term'));
    }
}
