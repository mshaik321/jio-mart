<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\WishlistItem;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $items = $request->user()->customer->wishlistItems()->with('variant.product.primaryImage')->get();

        return $items->map(fn (WishlistItem $item) => [
            'id' => $item->id,
            'product_variant_id' => $item->product_variant_id,
            'product_name' => $item->variant->product->name,
            'variant_name' => $item->variant->variant_name,
            'image_url' => $item->variant->product->primaryImage?->url(),
            'selling_price' => (float) $item->variant->selling_price,
        ]);
    }

    public function store(Request $request, ProductVariant $variant)
    {
        $item = WishlistItem::query()->firstOrCreate([
            'customer_id' => $request->user()->customer->id,
            'product_variant_id' => $variant->id,
        ]);

        return response()->json(['id' => $item->id], 201);
    }

    public function destroy(Request $request, ProductVariant $variant)
    {
        WishlistItem::query()
            ->where('customer_id', $request->user()->customer->id)
            ->where('product_variant_id', $variant->id)
            ->delete();

        return response()->json(['message' => 'Removed from wishlist.']);
    }
}
