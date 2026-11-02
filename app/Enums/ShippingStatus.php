<?php

namespace App\Enums;

enum ShippingStatus: string
{
    case Waiting = 'waiting';
    case Selected = 'selected';
    case ReadyToShip = 'ready_to_ship';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case CodRequested = 'cod_requested';
    case CodAgreed = 'cod_agreed';
    case CodCompleted = 'cod_completed';
}
