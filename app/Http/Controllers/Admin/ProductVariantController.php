<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InventoryMovementType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function store(Request $request, Product $product, InventoryService $inventory)
    {
        $this->authorize('products.update');

        $data = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:product_variants,sku'],
            'variant_name' => ['required', 'string', 'max:100'],
            'mrp' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'initial_quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($data['is_default'] ?? false) {
            $product->variants()->update(['is_default' => false]);
        }

        $variant = $product->variants()->create([
            'sku' => $data['sku'],
            'variant_name' => $data['variant_name'],
            'mrp' => $data['mrp'],
            'selling_price' => $data['selling_price'],
            'is_default' => $data['is_default'] ?? false,
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (! empty($data['initial_quantity'])) {
            $warehouse = Warehouse::query()->where('is_active', true)->firstOrFail();
            $inventory->recordMovement(
                $warehouse, $variant, InventoryMovementType::Adjustment,
                (int) $data['initial_quantity'], null, 'Initial stock on variant creation', $request->user()->id,
            );
        }

        return redirect()->route('admin.products.edit', $product)->with('status', 'Variant added.');
    }

    public function update(Request $request, Product $product, ProductVariant $variant)
    {
        $this->authorize('products.update');

        $data = $request->validate([
            'variant_name' => ['required', 'string', 'max:100'],
            'mrp' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($data['is_default'] ?? false) {
            $product->variants()->where('id', '!=', $variant->id)->update(['is_default' => false]);
        }

        $variant->update($data);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Variant updated.');
    }

    public function destroy(Product $product, ProductVariant $variant)
    {
        $this->authorize('products.update');

        $variant->delete();

        return redirect()->route('admin.products.edit', $product)->with('status', 'Variant removed.');
    }
}
