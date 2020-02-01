<?php

namespace App\Library\ArrayModel\Abstracts;

use ArrayAccess,Countable,Iterator ;

/**
 * Class SortedList
 * @package App\Library\ArrayModel\Abstracts
 */
class SortedList implements ArrayAccess,Countable,Iterator
{

    /**
     * Data : Keep the data of Array
     *
     * @var $data SortedList[]
     * @access protected
     */
    protected $data = [];

    /**
     * properties : Keep the array of properties
     *
     * @var array
     * @access protected
     *
     */
    protected $properties = [];

    /**
     * key : Indicating which key is used for the index(1st key of array);
     *
     * @var string
     * @access public
     */
    public $_key = '';

    /**
     * SortedList constructor.
     * @param $data
     *
     */
    protected function __construct($data = null)
    {
        if(null !== $data)
            $this->data = $data;
    }

    /**
     * Create a instance
     *
     * @param null $data
     * @return static
     */
    public static function of($data = null){
        $instance = new static($data);
        return $instance;
    }

    /**
     * Create a instance with specified key
     *
     * @param $src_array
     * @param $key
     * @return SortedList
     */
    public static function ofKey($src_array, $key = 'key'){
        $result_array = [];
        foreach($src_array as $item){
            $result_array[$item[$key]] = $item;
        }
        $instance = new static($result_array);
        return $instance;
    }

    /**
     * Find the row  with specified array of values of keys
     *
     * @param array $keys
     * @param int $level
     * @return mixed
     */
    public function findByKeys($keys,$level = 0){
        $key = $keys[$level];
        if(isset($this->data[$key])){
            if($this->data[$key] instanceof SortedList){
                /* @var $data SortedList[] */
                return $this->data[$key]->findByKeys($keys,$level+1);
            }else{
                return $this->data[$key];
            }
        }
        return false;
    }

    /**
     * Find the row with specified path which is dot-separated string.
     *
     * @param string $path
     * @return mixed
     */
    public function findByPath($path){
        $keys = explode('.', $path ."");
        return $this->findByKeys($keys);
    }

    /**
     * Find the row with specified condition array which is dot-separated string.
     *
     * @param $conditions
     * @param int $level
     * @return bool
     */
    public function findByConditions($conditions,$level=0){
        $key = $conditions[$this->_key];
        if(isset($this->data[$key])){
            if($this->data[$key] instanceof SortedList){
                return $this->data[$key]->findByKeys($conditions,$level+1);
            }else{
                return $this->data[$key];
            }
        }
        return false;
    }

    /**
     * Create a empty row with specified keys
     *
     * @param $keys
     * @param string $default
     * @return SortedList
     */
    public function createEmpty($keys,$default=''){
        $values = array_fill(0, count($keys), $default);
        $data[0] = array_combine($keys, $values);
        $instance = new self($data);
        return $instance;
    }

    /**
     * get a value from the specified property
     * @param $key
     * @return null
     */
    public function __get($key) {
        if(isset($this->properties[$key])){
            return $this->properties[$key];
        }
        return null;
    }

    /**
     * Assigns a property value to the specified data
     * @param $key
     * @param $value
     * @access public
     */
    public function __set($key,$value) {
        $this->properties[$key] = $value;
    }

    /**
     * Whether or not an data exists by key
     *
     * @param string ,An data key to check for
     * @access public
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function __isset ($key) {
        return isset($this->data[$key]);
    }

    /**
     * Unsets an data by key
     *
     * @param string ,The key to unset
     * @access public
     */
    public function __unset($key) {
        unset($this->data[$key]);
    }

    /**
     * Assigns a value to the specified offset
     *
     * @param string $offset ,The offset to assign the value to
     * @param mixed  $value ,The value to set
     * @access public
     * @abstracting ArrayAccess
     */
    public function offsetSet($offset,$value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * Whether or not an offset exists
     *
     * @param string $offset ,An offset to check for
     * @access public
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    /**
     * Unsets an offset
     *
     * @param string $offset ,The offset to unset
     * @access public
     * @abstracting ArrayAccess
     */
    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
        }
    }

    /**
     * Returns the value at specified offset
     *
     * @param string $offset ,The offset to retrieve
     * @access public
     * @return mixed
     * @abstracting ArrayAccess
     */
    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    /**
     * @return int|void
     */
    public function count(){
        return count($this->data);
    }

    /**
     * @return mixed|void
     */
    function rewind() {
        return reset($this->data);
    }

    /**
     * @return mixed
     */
    function current() {
        return current($this->data);
    }

    /**
     * @return int|mixed|string|null
     */
    function key() {
        return key($this->data);
    }

    /**
     * @return mixed|void
     */
    function next() {
        return next($this->data);
    }

    /**
     * @return bool
     */
    function valid() {
        return key($this->data) !== null;
    }

    /**
     * @return array
     */
    public function toArray(){
        $result = [];
        foreach($this->data as $key => $value){
            if($value instanceof SortedList){
                $result[$key] = $value->toArray();
            }else{
                $result = $value;
            }
        }
        return $result;
    }

    /**
     * @return false|string
     */
    public function toString(){
        return json_encode($this->toArray());
    }

    /**
     * @return string
     */
    public function __toString(){
        return $this->toString() . "";
    }

}