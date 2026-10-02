<?php

namespace App\Enums;

enum DeliveryAgentStatus: string
{
    case Available = 'available';
    case Busy = 'busy';
    case Offline = 'offline';
}
