<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private InventoryService $inventory) {}

    public function updateStatus(Order $order, OrderStatus $status, ?User $actor = null, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $status, $actor, $note) {
            $order->status = $status;
            $order->save();

            $order->statusHistories()->create([
                'status' => $status,
                'note' => $note,
                'changed_by' => $actor?->id,
                'created_at' => now(),
            ]);

            return $order;
        });
    }

    public function cancel(Order $order, ?User $actor = null, ?string $reason = null): Order
    {
        if (! $order->status->isCancellable()) {
            throw ValidationException::withMessages(['order' => 'This order can no longer be cancelled.']);
        }

        return DB::transaction(function () use ($order, $actor, $reason) {
            $warehouse = Warehouse::query()->where('is_active', true)->firstOrFail();

            foreach ($order->items as $item) {
                if (! $item->product_variant_id) {
                    continue;
                }

                $this->inventory->recordMovement(
                    $warehouse,
                    $item->variant,
                    InventoryMovementType::SaleCancellation,
                    $item->quantity,
                    $order,
                    "Cancellation of order {$order->order_number}",
                    $actor?->id,
                );
            }

            $order->status = OrderStatus::Cancelled;
            $order->cancelled_at = now();
            $order->cancel_reason = $reason;

            if ($order->payment_status === OrderPaymentStatus::Paid) {
                $order->payment_status = OrderPaymentStatus::Refunded;
            }

            $order->save();

            $order->statusHistories()->create([
                'status' => OrderStatus::Cancelled,
                'note' => $reason ?? 'Order cancelled.',
                'changed_by' => $actor?->id,
                'created_at' => now(),
            ]);

            return $order;
        });
    }
}
