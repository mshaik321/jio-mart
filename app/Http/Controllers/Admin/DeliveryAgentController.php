<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use Illuminate\Http\Request;

class DeliveryAgentController extends Controller
{
    public function index()
    {
        $this->authorize('delivery.manage');

        $agents = DeliveryAgent::query()->orderBy('name')->paginate(20);

        return view('admin.delivery-agents.index', compact('agents'));
    }

    public function create()
    {
        $this->authorize('delivery.manage');

        return view('admin.delivery-agents.create');
    }

    public function store(Request $request)
    {
        $this->authorize('delivery.manage');

        DeliveryAgent::create($this->validated($request));

        return redirect()->route('admin.delivery-agents.index')->with('status', 'Delivery agent added.');
    }

    public function edit(DeliveryAgent $deliveryAgent)
    {
        $this->authorize('delivery.manage');

        return view('admin.delivery-agents.edit', ['agent' => $deliveryAgent]);
    }

    public function update(Request $request, DeliveryAgent $deliveryAgent)
    {
        $this->authorize('delivery.manage');

        $deliveryAgent->update($this->validated($request));

        return redirect()->route('admin.delivery-agents.index')->with('status', 'Delivery agent updated.');
    }

    public function destroy(DeliveryAgent $deliveryAgent)
    {
        $this->authorize('delivery.manage');

        $deliveryAgent->delete();

        return redirect()->route('admin.delivery-agents.index')->with('status', 'Delivery agent removed.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'vehicle_type' => ['nullable', 'string', 'max:50'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // An unchecked "Active" box isn't submitted; treat its absence as false so records can be deactivated.
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
