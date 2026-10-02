<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function __construct(private InventoryService $inventory) {}

    /**
     * @param  array<int, int>  $receipts  purchase_order_item_id => quantity received now
     */
    public function receive(PurchaseOrder $purchaseOrder, array $receipts, ?User $actor = null): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $receipts, $actor) {
            foreach ($receipts as $itemId => $qty) {
                if ($qty <= 0) {
                    continue;
                }

                $item = $purchaseOrder->items()->where('id', $itemId)->lockForUpdate()->firstOrFail();

                if ($item->remainingQuantity() < $qty) {
                    throw ValidationException::withMessages([
                        'receipt' => "Cannot receive more than ordered for {$item->variant->sku}.",
                    ]);
                }

                $item->quantity_received += $qty;
                $item->save();

                $this->inventory->recordMovement(
                    $purchaseOrder->warehouse,
                    $item->variant,
                    InventoryMovementType::Purchase,
                    $qty,
                    $purchaseOrder,
                    "Receipt against PO {$purchaseOrder->po_number}",
                    $actor?->id,
                );
            }

            $purchaseOrder->refresh();
            $items = $purchaseOrder->items;
            $allReceived = $items->every(fn ($i) => $i->quantity_received >= $i->quantity_ordered);
            $anyReceived = $items->sum('quantity_received') > 0;

            $purchaseOrder->status = $allReceived
                ? PurchaseOrderStatus::Received
                : ($anyReceived ? PurchaseOrderStatus::PartiallyReceived : $purchaseOrder->status);
            $purchaseOrder->save();

            return $purchaseOrder;
        });
    }
}
