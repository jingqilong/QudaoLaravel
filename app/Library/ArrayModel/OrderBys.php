<?php


namespace App\Library\ArrayModel;


use InvalidArgumentException;

class OrderBys extends SortedList
{
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
        return new static($result);
    }
}