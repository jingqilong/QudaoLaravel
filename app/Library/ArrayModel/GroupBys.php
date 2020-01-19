<?php


namespace App\Library\ArrayModel;


use InvalidArgumentException;

class GroupBys extends SortedList
{
    /**
     * @param string ...$group_bys
     * @return SortedList
     * @throws InvalidArgumentException
     */
    public static function init(string ...$group_bys){
        $result = $columns =  [];
        foreach ($group_bys as $group_by){
            $exploded = explode(".", preg_replace('/\s+/', '', $group_by));
            if(2 !== count($exploded)){
                throw new InvalidArgumentException("incorrect value for '$group_by'! should be 'table.field'");
            }
            list($alias,$name) = $exploded;
            $result[$alias][$name] = ['alias'=>$alias,$name=>$name];
        }
        return new static($result);
    }
}