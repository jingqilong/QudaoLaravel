<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2020-02-05
 * Time: 1:56
 */

namespace App\Library\ArrayModel\Abstracts;

use ArrayIterator;

/**
 * Class KeyArrayIterator
 * @package App\Library\ArrayModel\Abstracts
 */
class KeyArrayIterator extends ArrayIterator
{
    /**
     * @var int
     */
    protected $key;

    /**
     * KeyArrayIterator constructor.
     * @param array $array
     * @param int $flags
     * @param int $key
     */
    public function __construct($array = array(), $flags = 0, $key = 0)
    {
        parent::__construct($array,$flags);
        $this->key = $key;
    }

    /**
     * @return int|null
     */
    public function key()
    {
        return parent::key() === null ? null : $this->key;
    }


}