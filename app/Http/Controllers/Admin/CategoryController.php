<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $this->authorize('categories.view');

        $categories = Category::query()->with('parent')->orderBy('sort_order')->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $this->authorize('categories.create');

        $parents = Category::query()->whereNull('parent_id')->orderBy('name')->get();

        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $this->authorize('categories.create');

        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(5);

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category)
    {
        $this->authorize('categories.update');

        $parents = Category::query()->whereNull('parent_id')->where('id', '!=', $category->id)->orderBy('name')->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('categories.update');

        $category->update($this->validated($request));

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $this->authorize('categories.delete');

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'parent_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);
    }
}
