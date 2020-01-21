<?php

namespace App\Library\ArrayModel\Components;

use App\Library\ArrayModel\Abstracts\SortedList;
use Closure;


/**
 * Class QueryList
 * @package App\Library\ArrayModel
 */
class QueryList extends SortedList
{
    /**
     * _fields
     *
     * @var Fields
     * @access public
     */
    public $_fields;

    /**
     * _from
     *
     * @var array
     * @access public
     */
    public $_from = [];

    /**
     * _alias
     *
     * @var string
     * @access public
     */
    public $_alias = '';

    /**
     * _from
     *
     * @var Join|null
     * @access public
     */
    public $_join = null;

    /**
     * _join_type
     *
     * @var int
     * @access public
     */
    public $_join_type = 0;

    /**
     * _ons
     *
     * @var Ons
     * @access public
     */
    public $_ons = [];

    /**
     * _wheres
     *
     * @var Wheres
     * @access public
     */
    public $_wheres = [];

    /**
     * _order_bys
     *
     * @var array
     * @access public
     */
    public $_order_bys;

    /**
     * _group_bys
     *
     * @var array
     * @access public
     */
    public $_group_bys;

    /**
     * _key
     *
     * @var string
     */
    public $_key;

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
    public function leftJoin($join_array, $alias){
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
     * @desc The format of arguments is
     *      Array:  ['a.table.field','b.table.field''] ,['a.table.field','b.table.field'']   ...
     *      Closure: function($query)use($data){
     *              $query->where(['table.field','=' 'value'])
     *      }
     * @param mixed ...$ons
     * @return $this
     */
    public function on(...$ons){
        $this->_getOn()->on(...$ons);
        return $this;
    }

    /**
     * @return mixed
     */
    public function onBrackets(){
        return $this->_getOn()->onBrackets();
    }

    /**
     * @param $on
     * @return $this
     */
    public function orOn($on){
        $this->_getOn()->orOn($on);
        return $this;
    }

    /**
     * @desc The format of arguments is
     *      Array:  ['a.table.field','=' 'value'] ,['b.table.field','asc']  ...
     *      Closure: function($query)use($data){
     *              $query->where(['table.field','=' 'value'])
     *      }
     * @param array|Closure ...$wheres
     * @return $this
     */
    public function where(...$wheres){
        $this->_getWhere()->where(...$wheres);
        return $this;
    }

    /**
     * @return Wheres
     */
    public function whereBrackets(){
        return $this->_getWhere()->whereBrackets();
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereIn($where){
        $this->_getWhere()->whereIn($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereNotIn($where){
        $this->_getWhere()->whereNotIn($where);
        return $this;
    }

    /***
     * @param $where
     * @return $this
     */
    public function orWhereNotIn($where){
        $this->_getWhere()->orWhereNotIn($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereIn($where){
        $this->_getWhere()->orWhereIn($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhere($where){
        $this->_getWhere()->orWhere($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereBetween($where){
        $this->_getWhere()->whereBetween($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereBetween($where){
        $this->_getWhere()->orWhereBetween($where);
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

    /**
     * @return QueryList
     */
    public function execute(){
        return $this->_build();
    }

    /**
     * @return QueryList
     */
    public function get(){
        return $this->_build();
    }

    /**
     * @desc For debug, you could check the sql.
     * @return string
     */
    public function toSql(){
        $result = "SELECT ";
        $result .= $this->_fields->_toSql();
        $result .= " FROM main_table " . $this->_alias;

        if($this->_join instanceof Join){
            $result .= $this->_join->_toSql();
            $result .= $this->_ons->toSql();
        }

        if($this->_wheres instanceof Wheres){
            $result .= $this->_wheres->toSql();
        }

        if($this->_order_bys instanceof OrderBys){
            $result .= $this->_order_bys->_toSql();
        }

        if($this->_group_bys instanceof GroupBys){
            $result .= $this->_group_bys->_toSql();
        }

        return $result;
    }

    /**
     * 传入 "key1.key2.key3" 进行搜索
     * @param $pathString
     * @return bool
     */
    public function findPath($pathString){
        $keys= explode('.',$pathString);
        return $this->findByKeys($keys);
    }
    /**
     * @param $alias
     * @param $field
     * @param array $result
     * @return array
     */
    public function pluck($alias,$field,&$result = []){
        if($this->_alias != $alias ){
            return $this->_join->pluck($alias,$field,$result);
        }
        foreach($this->data as $item){
            if($item instanceof QueryList){
                $result[] = $item->pluck($alias,$field,$result);
            }else{
                $result[] = $item[$field]??'';
            }
        }
        return $result;
    }


    /**
     * @return Wheres
     */
    public function _getWhere(){
        if(null === $this->_wheres){
            $this->_wheres = Wheres::of();
            $this->_wheres->_node_type = Wheres::NODE_TYPE_AGGREGATE;
        }
        return $this->_wheres;
    }

    /**
     * @return Ons
     */
    public function _getOn(){
        if(null === $this->_ons){
            $this->_ons = Ons::of();
        }
        return $this->_ons;
    }

    /**
     * @param $alias
     * @return array
     */
    public function getOrderByFields($alias){
        if($this->_order_bys instanceof OrderBys){
            return $this->_order_bys->getOrderByFields($alias);
        }
        return [];
    }

    /**
     * @param null $keys
     * @param int $level
     * @return array|null
     */
    public function getForeignKeys(&$keys=null,$level=0){
        if($this->_ons instanceof Ons){
            return $this->_ons->getForeignKeys($keys,$level);
        }
        return $keys;
    }

    /**
     * @param null $keys
     * @param int $level
     * @return array|null
     */
    public function getLocalKeys(&$keys=null,$level=0){
        if($this->_ons instanceof Ons){
            return $this->_ons->getLocalKeys($keys,$level);
        }
        return $keys;
    }

    /**
     * @return bool
     */
    private function _initJoin(){
        $join = $this->_join;
        $fields = $this->_fields[$join->_alias];
        $join->_fields[$join->_alias] = $fields;
        return true;
    }

    /**
     * @param $order_bys
     * @return bool
     */
    public function keySort($order_bys){
        $order_by = 'asc';
        if(isset($order_bys[$this->_key])){
            $order_by = $order_bys[$this->_key];
        }
        if('desc' == $order_by){
            krsort($this->data);
        }else{
            ksort($this->data);
        }
        foreach($this->data as $item){
            if($item instanceof QueryList){
                $item->keySort($order_bys);
            }
        }
        return true;
    }

    /**
     * @param $item
     * @param $path
     * @param int $level
     * @return bool
     */
    public function addItem($item,$path,$level=0){
        $key = $item[$path[$level]] ?? '';
        if('' == $key){
            $this->data[] = $item;
            return true;
        }
        $this->_key = $key;
        if (!isset($this->data[$key])) {
            $this->data[$key] = QueryList::of();
        }
        $child = $this->data[$key];
        return $child->addItem($item,$path,$level+1);
    }

    /**
     * @desc load the data for sort and join
     * @return bool
     */
    private function loadData(){
        $path = array_merge($this->_group_bys[$this->_alias], $this->getOrderByFields($this->_alias));
        foreach($this->_from[$this->_alias] as $item){
            $this->addItem($item,$path);
        }
        $order_bys = $this->_order_bys[$this->_alias];
        $this->keySort($order_bys);
        return true;
    }

    /**
     * @desc load the data for sort and join
     * @return bool
     */
    private function loadJoinData(){
        $path = array_merge($this->getLocalKeys(), $this->getOrderByFields($this->_alias));
        foreach($this->_from[$this->_alias] as $item){
            $this->addItem($item,$path);
        }
        $order_bys = $this->_order_bys[$this->_alias];
        $this->keySort($order_bys);
        return true;
    }

    /**
     * @return $this
     */
    private function _build(){
        if($this->_join instanceof QueryList){
            $this->_initJoin();
            $this->_join->loadJoinData();
        }
        $this->loadData();
        return $this;
    }

    /**
     * @param $result
     * @param $fields
     * @return bool
     */
    public function readFields(&$result,$fields){
        foreach($this->data as $item){
            if($item instanceof QueryList){
                return $item->readFields($result,$fields);
            }else{
                $new_item = [];
                foreach($fields as $field){
                    $new_item = $item[$field]??'';
                }
                $result[]= $new_item;
            }
        }
        return true;
    }

    /**
     * 一箇鍵值有一第記錄
     * @param $src_array
     * @param $key
     * @return array
     */
    public static function oneOfKey($src_array,$key){
        $result_array = [];
        foreach($src_array as $item){
            $result_array[$item[$key]] = $item;
        }
        return $result_array;
    }

    /**
     * 每条记录使用多个键值进行排序。
     * @param $src_array
     * @param $keys
     * @param $level
     * @return array
     */
    public static function oneOfKeys($src_array,$keys,$level = 0){
        $result_array = [];
        foreach($src_array as $item){
            if($level < count($keys) -1 ){
                $result_array[$item[$keys[$level]]] = QueryList::oneOfKeys($item,$keys,$level+1);
            }else{
                $result_array[$item[$keys[$level]]] = $item;
            }
        }
        return $result_array;
    }

    /**
     * 通過一箇鍵查到多條記錄幷存在此鍵值爲KEY數組中
     * @param $src_array
     * @param $key
     * @return array
     */
    public static function manyOfKey($src_array,$key){
        $result_array = [];
        foreach($src_array as $item){
            $new_item = & $result_array[$item[$key]];
            $new_item[] = $item;
        }
        return $result_array;
    }

}