<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()->where('is_active', true)->with('translations')->orderBy('sort_order')->get();

        return CategoryResource::collection($categories);
    }

    public function show(Category $category)
    {
        abort_unless($category->is_active, 404);

        return new CategoryResource($category->load('translations'));
    }
}
