<?php

namespace App\Exceptions\PayException;
use Exception;

class OrderUpdateFailedException extends Exception
{
    protected $code = 100;

    protected $message = 'Order update failed!';
}