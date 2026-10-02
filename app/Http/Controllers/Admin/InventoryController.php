<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InventoryMovementType;
use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Services\AuditLogService;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private AuditLogService $auditLog) {}

    public function index(Request $request)
    {
        $this->authorize('inventory.view');

        $inventories = Inventory::query()
            ->with('variant.product', 'warehouse')
            ->when($request->query('low_stock'), fn ($q) => $q->whereColumn('quantity_on_hand', '<=', 'low_stock_threshold'))
            ->when($request->query('q'), function ($q, $term) {
                $q->whereHas('variant.product', fn ($p) => $p->where('name', 'like', "%{$term}%"));
            })
            ->paginate(25)
            ->withQueryString();

        return view('admin.inventory.index', compact('inventories'));
    }

    public function adjust(Request $request, InventoryService $inventoryService)
    {
        $this->authorize('inventory.adjust');

        $data = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'not_in:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $warehouse = Warehouse::findOrFail($data['warehouse_id']);
        $variant = ProductVariant::findOrFail($data['product_variant_id']);

        $inventoryService->recordMovement(
            $warehouse, $variant, InventoryMovementType::Adjustment,
            (int) $data['quantity'], null, $data['note'] ?? 'Manual adjustment', $request->user()->id,
        );

        $this->auditLog->log($request->user(), 'inventory.adjusted', $variant, [], [
            'warehouse_id' => $warehouse->id, 'quantity' => $data['quantity'], 'note' => $data['note'] ?? null,
        ]);

        return redirect()->route('admin.inventory.index')->with('status', 'Stock adjusted.');
    }

    public function movements(ProductVariant $variant)
    {
        $this->authorize('inventory.view');

        $movements = InventoryMovement::query()
            ->where('product_variant_id', $variant->id)
            ->with('warehouse', 'creator')
            ->latest('created_at')
            ->paginate(30);

        return view('admin.inventory.movements', compact('variant', 'movements'));
    }
}
