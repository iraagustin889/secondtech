<?php

namespace App\Exceptions;

use Exception;

class PaymentGatewayException extends Exception
{
    public function __construct(string $message = 'Gerbang pembayaran belum dapat dihubungi. Silakan coba lagi.')
    {
        parent::__construct($message);
    }
}
