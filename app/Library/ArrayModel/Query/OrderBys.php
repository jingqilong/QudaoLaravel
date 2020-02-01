<?php

namespace App\Library\ArrayModel\Query;

use App\Library\ArrayModel\Abstracts\FieldList;
use InvalidArgumentException;

/**
 * Class OrderBys
 * @package App\Library\ArrayModel\Query
 */
class OrderBys extends FieldList
{
    /**
     * For debugging, import the sql clause.
     *
     * @var
     */
    public $order_by_string;

    /**
     * @param string[] ...$order_bys
     * @return OrderBys
     * @throws InvalidArgumentException
     */
    public static function init(...$order_bys){
        $result = $columns =  [];
        foreach ($order_bys as $order_by){
            $direction = $order_by[1]??"desc";
            list($alias,$name) = self::extractField($order_by[0]);
            $result[$alias][] = [$name => $direction];
        }
        $instance = new static($result);
        $instance->fields_string  = implode(',',$order_bys);
        return $instance;
    }

    /**
     * get the fields to create index.
     *
     * @param $alias
     * @return array
     */
    public function getOrderByFields($alias){
        $order_bys = parent::getFields($alias);
        return array_keys($order_bys);
    }

    /**
     * For debugging, import the sql clause.
     *
     * @return string
     */
    public function _toSql(){
        return " ORDER BY " .  $this->fields_string;
    }
}