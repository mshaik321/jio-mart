<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function submit(Customer $customer, Product $product, int $rating, ?string $title, ?string $body): Review
    {
        if ($customer->reviews()->where('product_id', $product->id)->exists()) {
            throw ValidationException::withMessages(['review' => 'You have already reviewed this product.']);
        }

        $orderItem = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($q) => $q->where('customer_id', $customer->id)->where('status', OrderStatus::Delivered))
            ->first();

        return Review::create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'order_item_id' => $orderItem?->id,
            'rating' => $rating,
            'title' => $title,
            'body' => $body,
            'is_verified_purchase' => (bool) $orderItem,
            'status' => ReviewStatus::Pending,
        ]);
    }

    public function moderate(Review $review, ReviewStatus $status): Review
    {
        $review->update(['status' => $status]);

        return $review;
    }
}
