<?php

namespace App\Exceptions;

use Exception;

class ShippingQuoteUnavailableException extends Exception
{
    public function __construct(string $message = 'Ongkos kirim tidak dapat diperoleh saat ini.')
    {
        parent::__construct($message);
    }
}
