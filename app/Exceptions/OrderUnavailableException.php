<?php

namespace App\Exceptions;

use Exception;

class OrderUnavailableException extends Exception
{
    public function __construct(string $message = 'Produk sudah tidak tersedia untuk dipesan.')
    {
        parent::__construct($message);
    }
}
