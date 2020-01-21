<?php


namespace App\Library\ArrayModel;

use InvalidArgumentException;

class Fields extends SortedList
{

    use FieldTrait;

    public $fields_string;
    /**
     * @param string ...$fields
     * @return SortedList
     * @throws InvalidArgumentException
     */
    public static function init(string ...$fields){
        $result = $columns =  [];
        foreach ($fields as $field){
            list($alias,$name) = self::extractField($field);
            $result[$alias][$name]  = ['alias'=>$alias,$name=>$name];
        }
        $instance =  new static($result);
        $instance->fields_string = implode(',',$fields);
        return $instance;
    }

    /**
     * @return string
     */
    public function _toSql(){
        return $this->fields_string;
    }

    /**
     * @param $alias
     * @return mixed
     */
    public function getFields($alias){
        return $this->data[$alias];
    }
}