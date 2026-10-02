<?php

namespace App\Enums;

enum ReturnStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case PickedUp = 'picked_up';
    case Inspected = 'inspected';
    case Refunded = 'refunded';
    case Completed = 'completed';
}
