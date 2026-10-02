<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product, ReviewService $reviews)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $customer = $request->user()->customer;

        if (! $customer) {
            throw ValidationException::withMessages(['review' => 'Only customers can submit reviews.']);
        }

        $reviews->submit($customer, $product, $data['rating'], $data['title'] ?? null, $data['body'] ?? null);

        return back()->with('status', 'Thanks for your review! It will appear once approved.');
    }
}
