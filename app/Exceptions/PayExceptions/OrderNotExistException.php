<?php

namespace App\Exceptions\PayException;
use Exception;

class OrderNotExistException extends Exception
{
    protected $code = 100;

    protected $message = 'Order not longer exist!';
}