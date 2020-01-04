<?php

namespace Tolawho\Loggy\Facades;

use Illuminate\Support\Facades\Facade;
use Tolawho\Loggy\Stream\Writer;

/**
 * @method static Writer  write($channel, $message, array $context = [])  Write out message by channel
 * Class Loggy
 * @package Tolawho\Loggy\Facades
 */
class Loggy extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'loggy';
    }
}
