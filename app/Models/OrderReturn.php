<?php

namespace App\Models;

use App\Enums\ReturnStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Maps to the `returns` table. Named OrderReturn because `Return` is a reserved PHP keyword.
 */
#[Fillable([
    'return_number', 'order_id', 'customer_id', 'status', 'reason', 'note',
    'refund_amount', 'requested_at', 'approved_at', 'completed_at',
])]
class OrderReturn extends Model
{
    protected $table = 'returns';

    protected function casts(): array
    {
        return [
            'status' => ReturnStatus::class,
            'refund_amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }
}
