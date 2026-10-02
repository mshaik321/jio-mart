<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        $this->authorize('brands.view');

        $brands = Brand::query()->orderBy('name')->paginate(20);

        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        $this->authorize('brands.create');

        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $this->authorize('brands.create');

        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(5);

        Brand::create($data);

        return redirect()->route('admin.brands.index')->with('status', 'Brand created.');
    }

    public function edit(Brand $brand)
    {
        $this->authorize('brands.update');

        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $this->authorize('brands.update');

        $brand->update($this->validated($request));

        return redirect()->route('admin.brands.index')->with('status', 'Brand updated.');
    }

    public function destroy(Brand $brand)
    {
        $this->authorize('brands.delete');

        $brand->delete();

        return redirect()->route('admin.brands.index')->with('status', 'Brand deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
