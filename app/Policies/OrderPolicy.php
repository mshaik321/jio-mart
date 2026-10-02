<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->can('orders.view') || $order->customer->user_id === $user->id;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $order->customer->user_id === $user->id && $order->status->isCancellable();
    }
}
