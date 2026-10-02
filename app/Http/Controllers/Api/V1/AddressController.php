<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return AddressResource::collection($request->user()->customer->addresses);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $customer = $request->user()->customer;

        if ($data['is_default'] ?? false) {
            $customer->addresses()->update(['is_default' => false]);
        }

        $address = $customer->addresses()->create($data);

        return new AddressResource($address);
    }

    public function update(Request $request, Address $address)
    {
        $this->authorize('update', $address);

        $data = $this->validated($request);

        if ($data['is_default'] ?? false) {
            $address->customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return new AddressResource($address);
    }

    public function destroy(Request $request, Address $address)
    {
        $this->authorize('delete', $address);

        $address->delete();

        return response()->json(['message' => 'Address removed.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:10'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }
}
