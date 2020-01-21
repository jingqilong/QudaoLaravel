<?php


namespace App\Library\ArrayModel\Components;

use App\Library\ArrayModel\Abstracts\SortedList;
use App\Library\ArrayModel\FieldTrait;
use InvalidArgumentException;

/**
 * Class Fields
 * @package App\Library\ArrayModel
 */
class Fields extends SortedList
{

    use FieldTrait;

    /**
     * For debugging , import sql clause
     *
     * fields_string
     *
     * @var
     */
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
     * For debugging , import sql clause
     *
     * @return string
     */
    public function _toSql(){
        return $this->fields_string;
    }

    /**
     * For function call
     *
     * @param $alias
     * @return mixed
     */
    public function getFields($alias){
        return $this->data[$alias];
    }
}