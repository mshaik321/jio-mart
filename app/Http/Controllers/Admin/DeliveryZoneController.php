<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;

class DeliveryZoneController extends Controller
{
    public function index()
    {
        $this->authorize('delivery.manage');

        $zones = DeliveryZone::query()->orderBy('name')->paginate(20);

        return view('admin.delivery-zones.index', compact('zones'));
    }

    public function create()
    {
        $this->authorize('delivery.manage');

        return view('admin.delivery-zones.create');
    }

    public function store(Request $request)
    {
        $this->authorize('delivery.manage');

        DeliveryZone::create($this->validated($request));

        return redirect()->route('admin.delivery-zones.index')->with('status', 'Delivery zone created.');
    }

    public function edit(DeliveryZone $deliveryZone)
    {
        $this->authorize('delivery.manage');

        return view('admin.delivery-zones.edit', ['zone' => $deliveryZone]);
    }

    public function update(Request $request, DeliveryZone $deliveryZone)
    {
        $this->authorize('delivery.manage');

        $deliveryZone->update($this->validated($request));

        return redirect()->route('admin.delivery-zones.index')->with('status', 'Delivery zone updated.');
    }

    public function destroy(DeliveryZone $deliveryZone)
    {
        $this->authorize('delivery.manage');

        $deliveryZone->delete();

        return redirect()->route('admin.delivery-zones.index')->with('status', 'Delivery zone deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'pincodes' => ['required', 'string'],
            'delivery_charge' => ['required', 'numeric', 'min:0'],
            'free_delivery_above' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // An unchecked "Active" box isn't submitted; treat its absence as false so records can be deactivated.
        $data['is_active'] = $request->boolean('is_active');
        $data['pincodes'] = array_values(array_filter(array_map('trim', explode(',', $data['pincodes']))));

        return $data;
    }
}
