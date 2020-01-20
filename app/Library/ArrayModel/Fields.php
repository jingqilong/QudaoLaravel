<?php


namespace App\Library\ArrayModel;

use InvalidArgumentException;

class Fields extends SortedList
{
    /**
     * @param string ...$fields
     * @return SortedList
     * @throws InvalidArgumentException
     */
    public static function init(string ...$fields){
        $result = $columns =  [];
        foreach ($fields as $field){
            $exploded = explode(".", preg_replace('/\s+/', '', $field));
            if(2 !== count($exploded)){
                throw new InvalidArgumentException("incorrect value for '$field'! should be 'table.field'");
            }
            list($alias,$name) = $exploded;
            $result[$alias][$name]  = ['alias'=>$alias,$name=>$name];
        }
        return new static($result);
    }

    /**
     * @return string
     */
    public function _toSql(){
        $result = '';
        foreach ($this->data as $alias => $fields){
            foreach ($fields as $field){
                $result .= " ". $alias . "." . $field .",";
            }
        }
        return rtrim($result, ',');
    }

    /**
     * @param $alias
     * @return mixed
     */
    public function getFields($alias){
        return $this->data[$alias];
    }
}