<?php


namespace App\Exceptions;


use Exception;

class FieldDoesNotExistException extends Exception
{
    protected $code = 100;

    protected $message = 'Field does not exist!';
}