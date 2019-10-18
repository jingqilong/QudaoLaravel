<?php


namespace App\Exceptions\ServiceException;


use Exception;

class EventDoesNotExistsException extends Exception
{
    protected $code = 100;

    protected $message = 'Event does not exist!';
}