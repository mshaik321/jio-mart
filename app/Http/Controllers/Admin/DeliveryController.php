<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeliveryStatus;
use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Models\Order;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function assign(Request $request, Order $order)
    {
        $this->authorize('delivery.manage');

        $data = $request->validate([
            'delivery_agent_id' => ['required', 'exists:delivery_agents,id'],
        ]);

        $delivery = $order->delivery ?? $order->delivery()->create([
            'delivery_zone_id' => $order->delivery_zone_id,
            'delivery_slot_id' => $order->delivery_slot_id,
            'status' => DeliveryStatus::Ready,
        ]);

        $delivery->update([
            'delivery_agent_id' => $data['delivery_agent_id'],
            'status' => DeliveryStatus::Assigned,
            'assigned_at' => now(),
        ]);

        DeliveryAgent::where('id', $data['delivery_agent_id'])->update(['status' => 'busy']);

        return back()->with('status', 'Delivery agent assigned.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorize('delivery.manage');

        $data = $request->validate([
            'status' => ['required', 'in:ready,assigned,picked_up,out_for_delivery,delivered,failed'],
        ]);

        $delivery = $order->delivery;
        $delivery->status = $data['status'];

        match ($data['status']) {
            'picked_up' => $delivery->picked_up_at = now(),
            'delivered' => $delivery->delivered_at = now(),
            default => null,
        };

        $delivery->save();

        return back()->with('status', 'Delivery status updated.');
    }
}
