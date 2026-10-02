<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * inventory_movements is an append-only ledger. inventories.quantity_on_hand is a
 * running snapshot that must only ever change together with a movement row, under
 * a row lock, so concurrent sales/returns/purchases can never desync the balance.
 */
class InventoryService
{
    public function recordMovement(
        Warehouse $warehouse,
        ProductVariant $variant,
        InventoryMovementType $type,
        int $quantity,
        ?Model $reference = null,
        ?string $note = null,
        ?int $userId = null,
    ): InventoryMovement {
        return DB::transaction(function () use ($warehouse, $variant, $type, $quantity, $reference, $note, $userId) {
            $inventory = Inventory::query()
                ->where('warehouse_id', $warehouse->id)
                ->where('product_variant_id', $variant->id)
                ->lockForUpdate()
                ->first();

            if (! $inventory) {
                $inventory = Inventory::create([
                    'warehouse_id' => $warehouse->id,
                    'product_variant_id' => $variant->id,
                    'quantity_on_hand' => 0,
                    'quantity_reserved' => 0,
                ]);
            }

            $newBalance = $inventory->quantity_on_hand + $quantity;

            if ($newBalance < 0) {
                throw new RuntimeException("Insufficient stock for variant {$variant->sku}.");
            }

            $inventory->quantity_on_hand = $newBalance;
            $inventory->save();

            return InventoryMovement::create([
                'warehouse_id' => $warehouse->id,
                'product_variant_id' => $variant->id,
                'type' => $type,
                'quantity' => $quantity,
                'balance_after' => $newBalance,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'note' => $note,
                'created_by' => $userId,
                'created_at' => now(),
            ]);
        });
    }
}
