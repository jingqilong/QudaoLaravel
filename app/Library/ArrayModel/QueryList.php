<?php


namespace App\Library\ArrayModel;


class QueryList extends SortedList
{
    /**
     * _fields
     *
     * @var Fields
     * @access private
     */
    private $_fields;

    /**
     * _from
     *
     * @var array
     * @access private
     */
    private $_from = [];

    /**
     * _alias
     *
     * @var string
     * @access private
     */
    public $_alias ='';

    /**
     * _from
     *
     * @var QueryList
     * @access private
     */
    private $_join = [];

    /**
     * _join_type
     *
     * @var int
     * @access private
     */
    public $_join_type = 0;

    /**
     * _ons
     *
     * @var array
     * @access private
     */
    private $_ons = [];

    /**
     * _wheres
     *
     * @var array
     * @access private
     */
    private $_wheres = [];

    /**
     * _order_bys
     *
     * @var string
     * @access private
     */
    private $_order_bys;

    /**
     * _group_bys
     *
     * @var string
     * @access private
     */
    private $_group_bys;

    /**
     * @desc The format of arguments is 'table.field','table.field' ...
     * by default "a.*" ,"b.*"
     * @param string ...$fields
     * @return $this
     */
    public function select(string ...$fields){
        $this->_fields =  Fields::init(...$fields);
        return $this;
    }

    /**
     * @param $src_array
     * @param $alias
     * @return $this
     */
    public function from($src_array, $alias){
        $this->_from[$alias] = $src_array;
        $this->_alias = $alias;
        return $this;
    }

    /**
     * @param $join_array
     * @param $alias
     * @return $this
     */
    public function join($join_array, $alias){
        $this->_join[$alias] = Join::init($join_array,$alias,Join::INNER_JOIN);
        return $this;
    }

    /**
     * @param $join_array
     * @param $alias
     * @return $this
     */
    public function LeftJoin($join_array, $alias){
        $this->_join[$alias]  = Join::init($join_array,$alias,Join::LEFT_JOIN);
        return $this;
    }

    /**
     * @param $join_array
     * @param $alias
     * @return $this
     */
    public function rightJoin($join_array, $alias){
        $this->_join[$alias]  = Join::init($join_array,$alias,Join::RIGHT_JOIN);
        return $this;
    }

    /**
     * @param $join_array
     * @param $alias
     * @return $this
     */
    public function innerJoin($join_array, $alias){
        $this->_join = Join::init($join_array,$alias,Join::INNER_JOIN);
        return $this;
    }

    /**
     * @param mixed ...$ons
     * @return $this
     */
    public function on(...$ons){
        $this->_ons = Ons::init(...$ons);
        return $this;
    }

    /**
     * @desc The format of arguments is ['table.field','=' 'value'] ,['table.field','asc']  ...
     * @param string ...$wheres
     * @return $this
     */
    public function where(string ...$wheres){
        $this->_wheres =  Wheres::init(...$wheres);
        return $this;
    }

    /**
     * @desc The format of arguments is ['table.field','asc'] ,['table.field','asc']  ...
     * @param array ...$order_bys
     * @return $this
     */
    public function orderBy(array ...$order_bys){
        $this->_order_bys =  OrderBys::init(...$order_bys);
        return $this;
    }

    /**
     * @param string ...$group_bys
     * @return $this
     */
    public function groupBy(string ...$group_bys ){
        $this->_group_bys =  GroupBys::init(...$group_bys);
        return $this;
    }


    private function _init_join(){
        $join = $this->_join;
        $fields = $this->_fields[$join->_alias];
        $join->select(...$fields);
        $wheres = $this->_wheres[$join->_alias];
        $join->where(...$wheres);

    }

    /**
     * @param \Closure|null $closure
     * @return $this
     */
    private function _build(\Closure $closure = null){
        if($this->_join instanceof QueryList){
            $this->_init_join();
        }
        return $this;
    }

    /**
     * @return QueryList
     */
    public function execute(\Closure $closure = null){
        return $this->_build();
    }

    /**
     * @return QueryList
     */
    public function get(\Closure $closure = null){
        return $this->_build();
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
        $instance = new static($result_array);
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
        $instance = new static($result_array);
        $instance->_order_bys = $keys[$level];
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
        $instance = new static($result_array);
        return $instance;
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

}