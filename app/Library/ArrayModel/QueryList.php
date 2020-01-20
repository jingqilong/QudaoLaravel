<?php


namespace App\Library\ArrayModel;

use Closure;

class QueryList extends SortedList
{
    use QueryTrait;
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
    public $_alias = '';

    /**
     * _from
     *
     * @var Join|null
     * @access private
     */
    private $_join = null;

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
     * @var Ons
     * @access private
     */
    private $_ons = [];

    /**
     * _wheres
     *
     * @var Wheres
     * @access private
     */
    private $_wheres = [];

    /**
     * _order_bys
     *
     * @var array
     * @access private
     */
    private $_order_bys;

    /**
     * _group_bys
     *
     * @var array
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
        $OnsObject = $this->_getOn();
        $logic = "and"; $i=0;
        foreach($ons as $on){
            $OnsObject->_addOn($on,'',$logic[$i]);
            $i=1;
        }
        return $this;
    }

    /**
     * @param $on
     */
    public function orOn($on){
        $this->_getOn()->_addOn($on,"","or");
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
        $WhereObject = $this->_getWhere();
        $logic = ['','and']; $i=0;
        foreach($wheres as $where){
            $WhereObject->_addWhere($where,null,$logic[$i]);
            $i=1;
        }
        return $this;
    }

    /**
     * @param $where
     */
    public function whereIn($where){
        $this->_getWhere()->_addWhere($where,'in','and');
    }

    /**
     * @param $where
     */
    public function whereNotIn($where){
        $this->_getWhere()->_addWhere($where,'notIn','and');
    }

    /***
     * @param $where
     */
    public function orWhereNotIn($where){
        $this->_getWhere()->_addWhere($where,'notIn','or');
    }

    /**
     * @param $where
     */
    public function orWhereIn($where){
        $this->_getWhere()->_addWhere($where,'in','or');
    }

    /**
     * @param $where
     */
    public function orWhere($where){
        $this->_getWhere()->_addWhere($where,'','or');
    }

    /**
     * @param $where
     */
    public function whereBetween($where){
        $wheres = [];
        list($field,$min,$max) = $where;
        $wheres[] = [$field,'>=',$min];
        $wheres[] = [$field,'<=',$max];
        $this->_getWhere()->_addWheres($wheres,'and','and');
    }

    /**
     * @param $where
     */
    public function orWhereBetween($where){
        $wheres = [];
        list($field,$min,$max) = $where;
        $wheres[] = [$field,'>=',$min];
        $wheres[] = [$field,'<=',$max];
        $this->_getWhere()->_addWheres($wheres,'and','or');
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
     * @param Closure|null $closure
     */
    private function _init_join(Closure $closure = null){
        $join = $this->_join;
        $fields = $this->_fields[$join->_alias];
        $join->select(...$fields);
        $wheres = $this->_wheres[$join->_alias];
        $join->where(...$wheres);

    }

    /**
     * @param Closure|null $closure
     * @return $this
     */
    private function _build(Closure $closure = null){
        if($this->_join instanceof QueryList){
            $this->_init_join($closure);
        }
        return $this;
    }

    /**
     * @param Closure|null $closure
     * @return QueryList
     */
    public function execute(Closure $closure = null){
        return $this->_build($closure);
    }

    /**
     * @param Closure|null $closure
     * @return QueryList
     */
    public function get(Closure $closure = null){
        return $this->_build($closure);
    }


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
     * @param $result
     * @param $fields
     * @return mixed
     */
    public function readFields(&$result,$fields){
        foreach($this->data as $item){
            if($item instanceof QueryList){
                return $item->readFields($result,$fields);
            }
        }
    }

    public function pluck($alias,$fields){
        if($this->_alias != $alias ){
            return $this->_join->pluck($alias,$fields);
        }

        $result = [];
        foreach($this->data as $item){
            if($item instanceof QueryList){
                return $item->readFields($result,$fields);
            }
        }
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
        if(isset($this->data[$keys[$level]])){
            return $this->data[$keys[$level]];
        }
        return [];
    }

}