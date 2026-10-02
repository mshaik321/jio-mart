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
            ->where('is_default', true)
            ->where('is_active', true)
            ->whereHas('product', function ($q) use ($term) {
                $q->where('status', ProductStatus::Active);

                if ($term !== '') {
                    $q->where(function ($inner) use ($term) {
                        $inner->where('name', 'like', "%{$term}%")
                            ->orWhere('sku', 'like', "%{$term}%")
                            ->orWhere('description', 'like', "%{$term}%");
                    });
                }
            })
            ->with(['product.primaryImage', 'product.brand', 'product.category']);

        $query = app(ProductFilters::class)->apply($query, $request);

        $variants = $query->paginate(24)->withQueryString();
        $brands = Brand::query()->where('is_active', true)->orderBy('name')->get();

        return view('storefront.search.index', compact('variants', 'brands', 'term'));
    }
}
