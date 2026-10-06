<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->where('status', ProductStatus::Active)
            ->with(['category', 'brand', 'unit', 'primaryImage', 'translations', 'variants' => fn ($q) => $q->where('is_active', true)->with('inventories')]);

        if ($term = $request->query('q')) {
            $query->matchingTerm((string) $term);
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->paginate((int) $request->query('per_page', 20));

        return ProductResource::collection($products);
    }

    public function show(Product $product)
    {
        abort_unless($product->status === ProductStatus::Active, 404);

        $product->load(['category', 'brand', 'unit', 'primaryImage', 'images', 'translations', 'variants' => fn ($q) => $q->where('is_active', true)->with('inventories')]);

        return new ProductResource($product);
    }
}
