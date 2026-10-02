<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->customer->addresses()->latest()->get();

        return view('storefront.addresses.index', compact('addresses'));
    }

    public function create()
    {
        return view('storefront.addresses.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $customer = $request->user()->customer;

        if ($data['is_default'] ?? false) {
            $customer->addresses()->update(['is_default' => false]);
        }

        $customer->addresses()->create($data);

        return redirect()->route('addresses.index')->with('status', 'Address added.');
    }

    public function edit(Address $address)
    {
        $this->authorize('update', $address);

        return view('storefront.addresses.edit', compact('address'));
    }

    public function update(Request $request, Address $address)
    {
        $this->authorize('update', $address);

        $data = $this->validated($request);

        if ($data['is_default'] ?? false) {
            $address->customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return redirect()->route('addresses.index')->with('status', 'Address updated.');
    }

    public function destroy(Address $address)
    {
        $this->authorize('delete', $address);

        $address->delete();

        return redirect()->route('addresses.index')->with('status', 'Address removed.');
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
