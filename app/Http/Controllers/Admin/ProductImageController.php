<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $this->authorize('products.update');

        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $path = $data['image']->store("products/{$product->id}", 'public');

        if ($data['is_primary'] ?? false) {
            $product->images()->update(['is_primary' => false]);
        }

        $product->images()->create([
            'path' => $path,
            'alt_text' => $product->name,
            'is_primary' => $data['is_primary'] ?? ($product->images()->count() === 0),
            'sort_order' => $product->images()->count(),
        ]);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Image uploaded.');
    }

    public function destroy(Product $product, ProductImage $image)
    {
        $this->authorize('products.update');

        abort_unless($image->product_id === $product->id, 404);

        \Illuminate\Support\Facades\Storage::disk('public')->delete($image->path);
        $image->delete();

        return redirect()->route('admin.products.edit', $product)->with('status', 'Image removed.');
    }

    public function makePrimary(Product $product, ProductImage $image)
    {
        $this->authorize('products.update');

        abort_unless($image->product_id === $product->id, 404);

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Primary image updated.');
    }
}
