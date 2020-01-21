<?php

namespace App\Library\ArrayModel;

use Closure;


/**
 * Class QueryList
 * @package App\Library\ArrayModel
 */
class QueryList extends SortedList
{
    use QueryTrait;
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


}