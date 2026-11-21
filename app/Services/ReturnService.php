<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\ReturnStatus;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReturnService
{
    public function __construct(private InventoryService $inventory) {}

    /**
     * @param  array<int, array{order_item_id: int, quantity: int, reason?: string}>  $items
     */
    public function request(Order $order, array $items, string $reason, ?string $note = null): OrderReturn
    {
        if ($order->status !== OrderStatus::Delivered) {
            throw ValidationException::withMessages(['order' => 'Only delivered orders can be returned.']);
        }

        if ($order->return()->exists()) {
            throw ValidationException::withMessages(['order' => 'A return has already been requested for this order.']);
        }

        $orderedQuantities = $order->items()->pluck('quantity', 'id');

        foreach ($items as $item) {
            $ordered = $orderedQuantities->get($item['order_item_id']);

            if ($ordered === null) {
                throw ValidationException::withMessages(['items' => 'One of the selected items does not belong to this order.']);
            }

            if ((int) $item['quantity'] > (int) $ordered) {
                throw ValidationException::withMessages(['items' => 'You cannot return more than the quantity you bought.']);
            }
        }

        return DB::transaction(function () use ($order, $items, $reason, $note) {
            $return = $order->customer->returns()->create([
                'return_number' => 'RET'.now()->format('Ymd').Str::upper(Str::random(6)),
                'order_id' => $order->id,
                'status' => ReturnStatus::Requested,
                'reason' => $reason,
                'note' => $note,
                'requested_at' => now(),
            ]);

            foreach ($items as $item) {
                $return->items()->create([
                    'order_item_id' => $item['order_item_id'],
                    'quantity' => $item['quantity'],
                    'reason' => $item['reason'] ?? null,
                ]);
            }

            return $return;
        });
    }

    public function approve(OrderReturn $return): OrderReturn
    {
        $this->ensureStatus($return, [ReturnStatus::Requested], 'Only requested returns can be approved.');

        $return->update(['status' => ReturnStatus::Approved, 'approved_at' => now()]);

        return $return;
    }

    public function reject(OrderReturn $return, ?string $note = null): OrderReturn
    {
        $this->ensureStatus($return, [ReturnStatus::Requested], 'Only requested returns can be rejected.');

        $return->update(['status' => ReturnStatus::Rejected, 'note' => $note]);

        return $return;
    }

    public function complete(OrderReturn $return, float $refundAmount, ?User $actor = null): OrderReturn
    {
        $this->ensureStatus(
            $return,
            [ReturnStatus::Approved, ReturnStatus::PickedUp, ReturnStatus::Inspected],
            'Only approved returns can be completed.',
        );

        if ($refundAmount > (float) $return->order->grand_total) {
            throw ValidationException::withMessages(['refund_amount' => 'The refund cannot be more than the order total.']);
        }

        return DB::transaction(function () use ($return, $refundAmount, $actor) {
            $warehouse = Warehouse::query()->where('is_active', true)->firstOrFail();

            foreach ($return->items as $returnItem) {
                $orderItem = $returnItem->orderItem;

                if (! $orderItem->product_variant_id) {
                    continue;
                }

                $this->inventory->recordMovement(
                    $warehouse,
                    $orderItem->variant,
                    InventoryMovementType::ReturnStock,
                    $returnItem->quantity,
                    $return,
                    "Return {$return->return_number}",
                    $actor?->id,
                );
            }

            $return->update([
                'status' => ReturnStatus::Completed,
                'refund_amount' => $refundAmount,
                'completed_at' => now(),
            ]);

            $return->order->update(['payment_status' => OrderPaymentStatus::Refunded]);

            return $return;
        });
    }

    /**
     * @param  list<ReturnStatus>  $allowed
     */
    private function ensureStatus(OrderReturn $return, array $allowed, string $message): void
    {
        if (! in_array($return->status, $allowed, true)) {
            throw ValidationException::withMessages(['return' => $message]);
        }
    }
}
