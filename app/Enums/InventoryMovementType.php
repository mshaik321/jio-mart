<?php

namespace App\Enums;

enum InventoryMovementType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case SaleCancellation = 'sale_cancellation';
    case ReturnStock = 'return';
    case Adjustment = 'adjustment';
    case Damage = 'damage';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
}
