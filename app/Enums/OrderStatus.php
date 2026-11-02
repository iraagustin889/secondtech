<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Paid = 'paid';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Received = 'received';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case CodRequested = 'cod_requested';
    case CodAgreed = 'cod_agreed';
    case CodCompleted = 'cod_completed';
}
