<?php


namespace App\Library\ArrayModel;


use InvalidArgumentException;

class OrderBys extends SortedList
{

    public $order_by_string;

    /**
     * @param array ...$order_bys
     * @return SortedList
     * @throws InvalidArgumentException
     */
    public static function init(array ...$order_bys){
        $result = $columns =  [];
        foreach ($order_bys as $order_by){
            if(2 !== count($order_by)){
                throw new InvalidArgumentException("incorrect value for '$order_by'! should be ['table.field','asc']");
            }
            $exploded = explode(".", preg_replace('/\s+/', '', $order_by[0]));
            if(2 !== count($exploded)){
                throw new InvalidArgumentException("incorrect value for '$order_by'! should be  ['table.field','asc']");
            }
            list($alias,$name) = $exploded;
            $result[$alias][$name] = ['alias'=>$alias, $name=>$name, 'type'=>$order_by[1]];
        }
        $instance = new static($result);
        $instance->order_by_string  = implode(',',$order_bys);
        return $instance;
    }

    /**
     * @param $alias
     * @return mixed
     */
    public function getOrderBys($alias){
        return $this->data[$alias];
    }

    /**
     * @return string
     */
    public function _toSql(){
        return "ORDER BY " .  $this->order_by_string;
    }
}