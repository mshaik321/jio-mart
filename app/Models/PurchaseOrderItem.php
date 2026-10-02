<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['purchase_order_id', 'product_variant_id', 'quantity_ordered', 'quantity_received', 'unit_cost'])]
class PurchaseOrderItem extends Model
{
    public $timestamps = true;

    protected function casts(): array
    {
        return ['unit_cost' => 'decimal:2'];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function remainingQuantity(): int
    {
        return $this->quantity_ordered - $this->quantity_received;
    }
}
