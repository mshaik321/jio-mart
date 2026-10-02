<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $this->authorize('purchases.view');

        $purchaseOrders = PurchaseOrder::query()->with('supplier', 'warehouse')->latest()->paginate(20);

        return view('admin.purchase-orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $this->authorize('purchases.create');

        $suppliers = Supplier::query()->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::query()->where('is_active', true)->orderBy('name')->get();
        $variants = ProductVariant::query()->with('product')->where('is_active', true)->orderBy('sku')->get();

        return view('admin.purchase-orders.create', compact('suppliers', 'warehouses', 'variants'));
    }

    public function store(Request $request)
    {
        $this->authorize('purchases.create');

        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.quantity_ordered' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO'.now()->format('Ymd').Str::upper(Str::random(5)),
            'supplier_id' => $data['supplier_id'],
            'warehouse_id' => $data['warehouse_id'],
            'status' => PurchaseOrderStatus::Ordered,
            'order_date' => $data['order_date'],
            'expected_date' => $data['expected_date'] ?? null,
            'created_by' => $request->user()->id,
            'total_amount' => collect($data['items'])->sum(fn ($i) => $i['quantity_ordered'] * $i['unit_cost']),
        ]);

        foreach ($data['items'] as $item) {
            $po->items()->create($item);
        }

        return redirect()->route('admin.purchase-orders.show', $po)->with('status', 'Purchase order created.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('purchases.view');

        $purchaseOrder->load('supplier', 'warehouse', 'items.variant.product');

        return view('admin.purchase-orders.show', compact('purchaseOrder'));
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder, PurchaseService $purchaseService)
    {
        $this->authorize('purchases.update');

        $data = $request->validate([
            'receive' => ['required', 'array'],
            'receive.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $receipts = array_filter(array_map('intval', $data['receive']));

        $purchaseService->receive($purchaseOrder, $receipts, $request->user());

        return redirect()->route('admin.purchase-orders.show', $purchaseOrder)->with('status', 'Stock received.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('purchases.delete');

        if ($purchaseOrder->status !== PurchaseOrderStatus::Draft) {
            return back()->withErrors(['purchase_order' => 'Only draft purchase orders can be deleted.']);
        }

        $purchaseOrder->delete();

        return redirect()->route('admin.purchase-orders.index')->with('status', 'Purchase order deleted.');
    }
}
