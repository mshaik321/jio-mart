<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $this->authorize('suppliers.view');

        $suppliers = Supplier::query()->orderBy('name')->paginate(20);

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $this->authorize('suppliers.create');

        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $this->authorize('suppliers.create');

        Supplier::create($this->validated($request));

        return redirect()->route('admin.suppliers.index')->with('status', 'Supplier created.');
    }

    public function edit(Supplier $supplier)
    {
        $this->authorize('suppliers.update');

        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $this->authorize('suppliers.update');

        $supplier->update($this->validated($request));

        return redirect()->route('admin.suppliers.index')->with('status', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        $this->authorize('suppliers.delete');

        $supplier->delete();

        return redirect()->route('admin.suppliers.index')->with('status', 'Supplier deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // An unchecked "Active" box isn't submitted; treat its absence as false so records can be deactivated.
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
