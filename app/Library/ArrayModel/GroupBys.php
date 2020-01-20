<?php


namespace App\Library\ArrayModel;


use InvalidArgumentException;

class GroupBys extends SortedList
{

    public $group_by_string;

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
        $instance =  new static($result);
        $instance->group_by_string  = implode(',',$group_bys);
        return $instance;
    }

    /**
     * @param $alias
     * @return mixed
     */
    public function getGroupBys($alias){
        return $this->data[$alias];
    }

    /**
     * @return string
     */
    public function _toSql(){
         return " GROUP BY " .  $this->group_by_string;
    }
}