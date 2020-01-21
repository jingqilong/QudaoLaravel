<?php

namespace App\Library\ArrayModel\Components;

use App\Library\ArrayModel\Abstracts\SortedList;
use App\Library\ArrayModel\FieldTrait;
use InvalidArgumentException;

/**
 * Class OrderBys
 * @package App\Library\ArrayModel
 */
class OrderBys extends SortedList
{
    use FieldTrait;

    /**
     * For debugging, import the sql clause.
     *
     * @var
     */
    public $order_by_string;

    /**
     * @param array ...$order_bys
     * @return SortedList
     * @throws InvalidArgumentException
     */
    public static function init(array ...$order_bys){
        $result = $columns =  [];
        foreach ($order_bys as $order_by){
            $direction = $order_by[1]??"desc";
            list($alias,$name) = self::extractField($order_by[0]);
            $result[$alias][] = [$name => $direction];
        }
        $instance = new static($result);
        $instance->order_by_string  = implode(',',$order_bys);
        return $instance;
    }

    /**
     * get the fields to create index.
     *
     * @param $alias
     * @return array
     */
    public function getOrderByFields($alias){
        $order_bys = $this->_order_bys[$alias];
        return array_keys($order_bys);
    }

    /**
     * For debugging, import the sql clause.
     *
     * @return string
     */
    public function _toSql(){
        return " ORDER BY " .  $this->order_by_string;
    }
}