<?php

namespace App\Exceptions\PayException;
use Exception;

class TradeUpdateFailedException extends Exception
{
    protected $code = 100;

    protected $message = 'Trade update failed!';
}