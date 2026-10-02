<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('reviews.view');

        $reviews = Review::query()
            ->with('product', 'customer.user')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function moderate(Request $request, Review $review, ReviewService $reviews)
    {
        $this->authorize('reviews.moderate');

        $data = $request->validate(['status' => ['required', 'in:approved,rejected']]);

        $reviews->moderate($review, ReviewStatus::from($data['status']));

        return back()->with('status', 'Review updated.');
    }
}
