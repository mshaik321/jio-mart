<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliverySlot;
use Illuminate\Http\Request;

class DeliverySlotController extends Controller
{
    public function index()
    {
        $this->authorize('delivery.manage');

        $slots = DeliverySlot::query()->orderBy('start_time')->paginate(20);

        return view('admin.delivery-slots.index', compact('slots'));
    }

    public function create()
    {
        $this->authorize('delivery.manage');

        return view('admin.delivery-slots.create');
    }

    public function store(Request $request)
    {
        $this->authorize('delivery.manage');

        DeliverySlot::create($this->validated($request));

        return redirect()->route('admin.delivery-slots.index')->with('status', 'Delivery slot created.');
    }

    public function edit(DeliverySlot $deliverySlot)
    {
        $this->authorize('delivery.manage');

        return view('admin.delivery-slots.edit', ['slot' => $deliverySlot]);
    }

    public function update(Request $request, DeliverySlot $deliverySlot)
    {
        $this->authorize('delivery.manage');

        $deliverySlot->update($this->validated($request));

        return redirect()->route('admin.delivery-slots.index')->with('status', 'Delivery slot updated.');
    }

    public function destroy(DeliverySlot $deliverySlot)
    {
        $this->authorize('delivery.manage');

        $deliverySlot->delete();

        return redirect()->route('admin.delivery-slots.index')->with('status', 'Delivery slot deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'max_orders' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // An unchecked "Active" box isn't submitted; treat its absence as false so records can be deactivated.
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
