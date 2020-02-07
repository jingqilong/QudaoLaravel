<?php

namespace App\Library\ArrayModel\Query;

use App\Library\ArrayModel\Abstracts\SortedList;
use Closure;

/**
 * Class QueryBuilder
 * @package App\Library\ArrayModel\Query
 * @author Bardeen
 */
class QueryBuilder extends SortedList
{
    /**
     * _fields
     *
     * @var Fields $_fields
     * @access public
     */
    protected $_fields;

    /**
     * _from
     *
     * @var array
     * @access public
     */
    protected $_from = [];

    /**
     * _alias
     *
     * @var string
     * @access public
     */
    protected $_alias = '';

    /**
     * _from
     *
     * @var Join|null
     * @access public
     */
    protected $_join = null;

    /**
     * _join_type
     *
     * @var int
     * @access public
     */
    protected $_join_type = 0;

    /**
     * _ons
     *
     * @var Ons $_ons
     * @access public
     */
    protected $_ons = null;

    /**
     * _wheres
     *
     * @var Wheres $_wheres
     * @access public
     */
    protected $_wheres = null;

    /**
     * _order_bys
     *
     * @var OrderBys $_order_bys
     * @access public
     */
    protected $_order_bys;

    /**
     * _result
     *
     * @var QueryBuilder
     */
    protected $_result;

    /**
     * @var array
     */
    protected $_array_filter = [];

    /**
     * @var array
     */
    protected $_join_closure = [];

    /**
     * QueryBuilder constructor.
     */
    public function __construct()
    {
        $this->_wheres = new Wheres(null);
        $this->_ons = new Ons(null);
        parent::__construct();
    }

    /**
     * @return mixed
     */
    protected function getJoinClosure(){
        return $this->_join_closure[0];
    }

    /**
     * @return Wheres
     */
    public function _getWhere(){
        return $this->_wheres;
    }

    /**
     * @return Ons
     */
    public function _getOn(){
        return $this->_ons;
    }

    /**
     * @desc The format of arguments is 'table.field','table.field' ...
     * by default "a.*" ,"b.*"
     * @param string[] ...$fields
     * @return $this
     */
    public function select(...$fields){
        $this->_fields =  Fields::newFields(...$fields);
        return $this;
    }

    /**
     * @param $left_src
     * @param $alias
     * @return $this
     */
    public function from($left_src, $alias){
        $this->_from[$alias] = $left_src;
        $this->_alias = $alias;
        return $this;
    }

    /**
     * @param $right_src
     * @param $alias
     * @return $this
     */
    public function join($right_src, $alias){
        $this->_join[$alias] = Join::newJoin($this,$right_src,$alias,Join::INNER_JOIN);
        return $this;
    }

    /**
     * @param $join_array
     * @param $alias
     * @return $this
     */
    public function leftJoin($join_array, $alias){
        $this->_join[$alias]  = Join::newJoin($this,$join_array,$alias,Join::LEFT_JOIN);
        return $this;
    }

    /**
     * @param $join_array
     * @param $alias
     * @return $this
     */
    public function innerJoin($join_array, $alias){
        $this->_join = Join::newJoin($this,$join_array,$alias,Join::INNER_JOIN);
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
     * @param $on
     * @return $this
     */
    public function orOn($on){
        $this->_getOn()->orOn($on);
        return $this;
    }

    /**
     * @param $on
     * @return $this
     */
    public function andOn($on){
        $this->_getOn()->andOn($on);
        return $this;
    }

    /**
     * @param $on
     * @return $this
     */
    public function onContains($on){
        $this->_getOn()->onContains($on);
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
     * @param $where
     * @return $this
     */
    public function whereIs($where){
        $this->_getWhere()->whereIs($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereIs($where){
        $this->_getWhere()->orWhereIs($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereIsNot($where){
        $this->_getWhere()->whereIsNot($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereIsNot($where){
        $this->_getWhere()->orWhereIsNot($where);
        return $this;
    }

    /**
     * @desc The format of arguments is ['table.field','asc'] ,['table.field','asc']  ...
     * @param array ...$order_bys
     * @return $this
     */
    public function orderBy(...$order_bys){
        $this->_order_bys =  OrderBys::newOrderBy(...$order_bys);
        return $this;
    }

    /**
     * @param Closure|null $closure
     * @return QueryBuilder
     */
    public function execute(Closure $closure = null){
        $this->_join_closure[0] = $closure;
        return $this->_build();
    }

    /**
     * @param Closure|null $closure
     * @return QueryBuilder
     */
    public function get(Closure $closure = null){
        $this->_join_closure[0] = $closure;
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
        return $result;
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
        return array_column($this->_from,$field);
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
     * @param $order_bys
     * @return bool
     */
    public function keySortOrderBy($order_bys){
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
            if($item instanceof QueryBuilder){
                $item->keySortOrderBy($order_bys);
            }
        }
        return $this;
    }

    /**
     * @desc load the data for sort and join
     * @return bool
     */
    protected function loadData(){
        $path = $this->getOrderByFields($this->_alias);
        foreach($this->_from[$this->_alias] as $item){
            $this->addDataItem($item,$path);
        }
        $order_bys = $this->_order_bys[$this->_alias];
        $this->keySortOrderBy($order_bys);
        return $this;
    }

    /**
     * @param $item
     * @param $path
     * @param int $level
     * @return bool
     */
    public function addDataItem($item,$path,$level=0){
        $key = $item[$path[$level]] ?? '';
        if('' == $key){
            $this->data[] = $item;
            return $this;
        }
        $this->_key = $key;
        if (!isset($this->data[$key])) {
            $this->data[$key] = QueryBuilder::of();
        }
        /** @var QueryBuilder $child */
        $child = $this->data[$key];
        return $child->addDataItem($item,$path,$level+1);
    }

    /**
     * @return $this
     */
    private function _build(){
        if($this->_join instanceof QueryBuilder){
            $this->_join->loadData();
        }
        $this->loadData();
        return $this->makeJoin();
    }

    /**
     * @return array
     */
    protected function getFieldsFilter(){
        if(empty($this->_array_filter)){
            if(null === $this->_fields){
                $this->_array_filter = ['*'];
            }
            $this->_array_filter = $this->_fields->getFilter();
        }
        return $this->_array_filter;
    }

    /**
     * @param $row
     * @return bool
     */
    protected function filterByWhere($row){
        return $this->_wheres->_getValue($row);
    }

    /**
     * @param Closure|null $closure
     * @return QueryBuilder (array|static)
     */
    protected function _query(Closure $closure = null){
        $this->_result = $result = QueryBuilder::of();;
        $main_list = $this;
        $with_closure = ($closure instanceof Closure);
        $this->getFieldsFilter();// select fields
        foreach($main_list as $item){
            if($with_closure){
                $item = $closure($closure);
            }
            $this->filterResult($result,$item);
        }
        return $result;
    }

    /**
     * @return QueryBuilder (array|static)
     */
    public function makeJoin(){
        $closure = $this->getJoinClosure();
        if(!$this->_join instanceof Join){
            return $this->_query($closure);
        }
        $this->_result = QueryBuilder::of();
        $left_items = $this;
        $this->getFieldsFilter();
        foreach($left_items as $left_item){
            $this->_join->joinItem($left_item,[$this,'mergeResult']);
        }
        return $this->_result;
    }

    /**
     * @param $result
     * @param $item
     */
    protected function filterResult(& $result,$item){
        if($this->filterByWhere($item)){// filter where
            if(['*'] === $this->_array_filter){// filter select
                $result[] = $item;
            }else{
                $result[] = array_intersect_key($item, $this->_array_filter);
            }
        }
    }

    /**
     * @param $left_item
     * @param $right_item
     */
    public function mergeResult($left_item,$right_item){
        if(empty($right_item)){
            if(Join::INNER_JOIN == $this->_join_type){
                return;
            }
            $right_item = $this->_join->getEmptyItem();
        }
        /** @var Closure $closure */
        $closure = $this->getJoinClosure();
        $with_closure = ($closure instanceof Closure);
        if($with_closure){
            $item = $closure($left_item,$right_item);
        }else{
            $item = array_merge($left_item,$right_item);
        }
        $this->filterResult($this->_result,$item);
    }
}