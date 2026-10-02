<?php

namespace App\Policies;

use App\Models\OrderReturn;
use App\Models\User;

class OrderReturnPolicy
{
    public function view(User $user, OrderReturn $orderReturn): bool
    {
        return $user->can('returns.view') || $orderReturn->customer->user_id === $user->id;
    }
}
