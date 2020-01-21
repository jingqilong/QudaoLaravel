<?php


namespace App\Library\ArrayModel;


use InvalidArgumentException;

class OrderBys extends SortedList
{
    use FieldTrait;

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
            $result[$alias][$name] = ['alias'=>$alias, $name=>$name, 'type'=>$direction];
        }
        $instance = new static($result);
        $instance->order_by_string  = implode(',',$order_bys);
        return $instance;
    }

    /**
     * @return string
     */
    public function _toSql(){
        return " ORDER BY " .  $this->order_by_string;
    }
}