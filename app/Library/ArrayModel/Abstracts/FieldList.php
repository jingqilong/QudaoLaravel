<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2020-01-22
 * Time: 10:13
 */

namespace App\Library\ArrayModel\Abstracts;

use InvalidArgumentException;
//use App\Library\ArrayModel\Abstracts\SortedList;
/**
 * Class FieldList
 * @package App\Library\ArrayModel\Abstracts
 */
class FieldList
{
    /**
     * Data : Keep the data of Array
     *
     * @var array
     * @access protected
     */
    protected $data;

    /**
     * For debugging , import sql clause
     *
     * fields_string
     *
     * @var
     */
    public $fields_string;

    /**
     * FieldList constructor.
     * @param $field_list
     */
    public function __construct($field_list)
    {
        foreach ($field_list as $alias => $fields){
            //using SortedList to save the items
            $new_items = SortedList::of($fields);
            $new_items->alias = $alias;
            $this->data[$alias] = $new_items;
        }
    }

    /**
     * get the alias from field string fastly.
     * @param $field
     * @return string
     */
    public function extractAlias($field){
        return strstr($field, '.', true);
    }

    /**
     * Extract the fields from string
     * @param $field
     * @return array
     */
    public static function extractField($field){
        $exploded = explode(".", preg_replace('/\s+/', '', $field));
        if(2 !== count($exploded)){
            throw new InvalidArgumentException("incorrect value for '$field'! should be 'table.field'");
        }
        return  $exploded;
    }

    /**
     * @param string ...$fields
     * @return SortedList
     * @throws InvalidArgumentException
     */
    public static function parse(string ...$fields){
        $result = $columns =  [];
        foreach ($fields as $field){
            list($alias,$name) = self::extractField($field);
            $result[$alias][]  = $name;
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
    public function getFields($alias = null){
        if(null == $alias ){
            return array_merge(...$this->data);
        }
        if(!isset($this->data[$alias])){
            //using SortedList to save the items
            $this->data[$alias] = SortedList::of();
        }
        return $this->data[$alias];
    }

    /**
     * @return array
     */
    public function getFilter(){
        $fields = $this ->getFields();
        $filter = array_flip($fields);
        return $filter;
    }

}