<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2020-02-05
 * Time: 1:49
 */

namespace App\Library\ArrayModel\Abstracts;

use ArrayAccess;
use IteratorAggregate;
use AppendIterator;

class MultiMap implements ArrayAccess, IteratorAggregate
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) { // $a[] = ...
            $this->data[] = array($value);
        } else {
            $this->data[$offset][] = $value;
        }
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /**
     * @return AppendIterator
     */
    public function getIterator()
    {
        $it = new AppendIterator();
        foreach ($this->data as $key => $values) {
            $it->append(new KeyArrayIterator($values, 0, $key));
        }
        return $it;
    }


}