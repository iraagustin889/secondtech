<?php

namespace App\Enums;

enum RefundStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
