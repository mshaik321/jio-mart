<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Ready = 'ready';
    case Assigned = 'assigned';
    case PickedUp = 'picked_up';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Failed = 'failed';
}
