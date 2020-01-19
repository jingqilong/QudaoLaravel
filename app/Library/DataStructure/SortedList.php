<?php


namespace App\Library\DataStructure;

use ArrayAccess,Countable,Iterator ;

class SortedList implements ArrayAccess,Countable,Iterator
{

    /**
     * Data
     *
     * @var array
     * @access private
     */
    private $data = [];

    /**
     * key
     *
     * @var string
     * @access private
     */
    private $key;

    /**
     * SortedList constructor.
     * @param $data
     */
    private function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * 一箇鍵值有一第記錄
     * @param $src_array
     * @param $key
     * @return SortedList
     */
    public static function oneOfKey($src_array,$key){
        $result_array = [];
        foreach($src_array as $item){
            $result_array[$item[$key]] = $item;
        }
        $instance = new self($result_array);
        return $instance;
    }

    /**
     * 每條記錄均使用多箇鍵值排序。
     * @param $src_array
     * @param $keys
     * @param $level
     * @return SortedList
     */
    public static function oneOfKeys($src_array,$keys,$level = 0){
        $result_array = [];
        foreach($src_array as $item){
            if($level < count($keys)-1 ){
                $result_array[$item[$keys[$level]]] = SortedList::oneOfKeys($item,$keys,$level+1);
            }else{
                $result_array[$item[$keys[$level]]] = $item;
            }
        }
        $instance = new self($result_array);
        $instance->key = $keys[$level];
        return $instance;
    }

    /**
     * 通過一箇鍵查到多條記錄幷存在此鍵值爲KEY數組中
     * @param $src_array
     * @param $key
     * @return SortedList
     */
    public static function manyOfKey($src_array,$key){
        $result_array = [];
        foreach($src_array as $item){
            $new_item = & $result_array[$item[$key]];
            $new_item[] = $item;
        }
        $instance = new self($result_array);
        return $instance;
    }

    /**
     * 創建一條以傳入的鍵生成的空記彔的對像
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
     * Get a data by key
     * 通過 $obj->key 獲得數據
     * @param $key  ,tring The key data to retrieve
     * @access public
     * @return mixed
     */
    public function __get ($key) {
        if(isset($this->data[$key])){
            return $this->data[$key];
        }
        $keys= explode('.',$key);
        return $this->findByKeys($keys);
    }

    /**
     * 傳入 "key1.key2.key3" 進行搜索
     * @param $keyString
     * @return array
     */
    public function getByKeyString($keyString){
        $keys= explode('.',$keyString);
        return $this->findByKeys($keys);
    }

    /**
     * 傳入['1','2','3'...]進行搜索
     * @param $keys ,從根節點到葉節點的KEY的數組
     * @param int $level
     * @return array
     */
    public function findByKeys($keys,$level=0){
         if($level < count($keys)-1 ){
             if($this->data[$keys[$level]] instanceof SortedList ){
                 return $this->data[$keys[$level]]->findByKeys($keys,$level+1);
             }
             if(is_array($this->data[$keys[$level]])){
                 return $this->data[$keys[$level]];
             }
         }
         if(isset($this->$this->data[$keys[$level]])){
             return $this->data[$keys[$level]];
         }
         return [];
    }

    /**
     * 通過 where(['key1'=>'value1','key2'=>'value2','key3'=>'value3'])進行搜索
     * @param $keys
     * @param int $level
     * @return array|mixed
     */
    public function where($keys,$level=0){
        $key = $keys[$this->key];
        if(isset($this->data[$key])){
            if($this->data[$key] instanceof SortedList ){
                return $this->data[$key]->where($keys,$level+1);
            }
            if(is_array($this->data[$keys[$level]])){
                return $this->data[$key];
            }
        }
        return [];
    }

    /**
     * Assigns a value to the specified data
     *
     * @param string The data key to assign the value to
     * @param mixed  The value to set
     * @access public
     */
    public function __set($key,$value) {
        $this->data[$key] = $value;
    }

    /**
     * Whether or not an data exists by key
     *
     * @param string An data key to check for
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
     * @param string The key to unset
     * @access public
     */
    public function __unset($key) {
        unset($this->data[$key]);
    }

    /**
     * Assigns a value to the specified offset
     *
     * @param string The offset to assign the value to
     * @param mixed  The value to set
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
     * @param string An offset to check for
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
     * @param string The offset to unset
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
     * @param string The offset to retrieve
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