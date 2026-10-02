<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->customer->orders()->with('items')->latest()->paginate(15);

        return OrderResource::collection($orders);
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        return new OrderResource($order->load('items', 'statusHistories'));
    }

    public function cancel(Request $request, Order $order, OrderService $orders)
    {
        $this->authorize('cancel', $order);

        try {
            $orders->cancel($order, $request->user(), 'Cancelled by customer via API.');
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return new OrderResource($order->fresh('items'));
    }
}
