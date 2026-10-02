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
            ->with(['category', 'brand', 'unit', 'primaryImage', 'variants' => fn ($q) => $q->where('is_active', true)]);

        if ($term = $request->query('q')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"));
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->paginate((int) $request->query('per_page', 20));

        return ProductResource::collection($products);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'unit', 'primaryImage', 'images', 'variants' => fn ($q) => $q->where('is_active', true)]);

        return new ProductResource($product);
    }
}
