<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('products.view');

        $products = Product::query()
            ->with('category', 'brand')
            ->when($request->query('q'), fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $this->authorize('products.create');

        return view('admin.products.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->authorize('products.create');

        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(5);

        $product = Product::create($data);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product created. Now add at least one variant.');
    }

    public function edit(Product $product)
    {
        $this->authorize('products.update');

        $product->load('variants.inventories.warehouse', 'images');

        return view('admin.products.edit', array_merge($this->formData(), compact('product')));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('products.update');

        $product->update($this->validated($request));

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('products.delete');

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    private function formData(): array
    {
        return [
            'categories' => Category::query()->orderBy('name')->get(),
            'brands' => Brand::query()->orderBy('name')->get(),
            'units' => Unit::query()->orderBy('name')->get(),
            'statuses' => ProductStatus::cases(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,'.($request->route('product')?->id ?? 'NULL')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'weight' => ['nullable', 'numeric'],
            'mrp' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'min_order_qty' => ['nullable', 'integer', 'min:1'],
            'max_order_qty' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,active,inactive'],
            'is_featured' => ['nullable', 'boolean'],
            'is_bestseller' => ['nullable', 'boolean'],
            'is_new_arrival' => ['nullable', 'boolean'],
        ]);
    }
}
