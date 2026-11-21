<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\ReturnService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->customer
            ->orders()
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('storefront.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['items', 'statusHistories', 'payments', 'delivery.agent', 'return']);

        return view('storefront.orders.show', compact('order'));
    }

    public function confirmation(Order $order)
    {
        $this->authorize('view', $order);

        $order->load('items');

        return view('storefront.orders.confirmation', compact('order'));
    }

    public function cancel(Order $order, OrderService $orders)
    {
        $this->authorize('cancel', $order);

        $orders->cancel($order, auth()->user(), 'Cancelled by customer.');

        return back()->with('status', 'Your order has been cancelled.');
    }

    public function requestReturn(Request $request, Order $order, ReturnService $returns)
    {
        $this->authorize('view', $order);

        // Unticked items still submit their hidden quantity field; keep only the rows the customer selected.
        $request->merge(['items' => array_values(array_filter(
            (array) $request->input('items', []),
            fn ($item) => is_array($item) && ! empty($item['order_item_id']),
        ))]);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer', 'exists:order_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $returns->request($order, $data['items'], $data['reason']);

        return back()->with('status', 'Your return request has been submitted.');
    }
}
