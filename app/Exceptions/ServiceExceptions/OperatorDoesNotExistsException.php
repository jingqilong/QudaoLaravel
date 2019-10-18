<?php


namespace App\Exceptions\ServiceException;


use Exception;

class OperatorDoesNotExistsException extends Exception
{
    protected $code = 100;

    protected $message = 'Operator does not exist!';
}