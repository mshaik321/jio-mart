<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('orders.view');

        $orders = Order::query()
            ->with('customer.user')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('q'), fn ($q, $term) => $q->where('order_number', 'like', "%{$term}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function show(Order $order)
    {
        $this->authorize('orders.view');

        $order->load('items', 'statusHistories.changedBy', 'payments', 'customer.user', 'delivery.agent', 'return');
        $agents = DeliveryAgent::query()->where('is_active', true)->get();

        return view('admin.orders.show', [
            'order' => $order,
            'statuses' => OrderStatus::cases(),
            'agents' => $agents,
        ]);
    }

    public function updateStatus(Request $request, Order $order, OrderService $orderService)
    {
        $this->authorize('orders.update');

        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_map(fn ($c) => $c->value, OrderStatus::cases()))],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $orderService->updateStatus($order, OrderStatus::from($data['status']), $request->user(), $data['note'] ?? null);

        return back()->with('status', 'Order status updated.');
    }

    public function cancel(Request $request, Order $order, OrderService $orderService)
    {
        $this->authorize('orders.cancel');

        $orderService->cancel($order, $request->user(), $request->input('reason', 'Cancelled by admin.'));

        return back()->with('status', 'Order cancelled.');
    }
}
