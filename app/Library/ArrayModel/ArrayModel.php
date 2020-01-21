<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2020-01-21
 * Time: 21:00
 */

namespace App\Library\ArrayModel;

use App\Library\ArrayModel\Components\QueryList;
use Closure;
use BadMethodCallException;

class ArrayModel
{
    /**
     * @var QueryList
     */
    public $query;

    /**
     * @var QueryList
     */
    public $result;


    public function __construct()
    {
        $this->query = QueryList::of();
        $this->result = QueryList::of();
    }

    /**
     * @param $name
     * @param $arguments
     */
    public function __call($name, $arguments)
    {
        if(method_exists($this,$name)){
            $this->$name(...$arguments);
        }
        throw new BadMethodCallException("Method " . $name . " not found! ");
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __callStatic($name, $arguments)
    {
        $instance = new static();
        return $instance->$name(...$arguments);
    }

    /**
     * @desc The format of arguments is 'table.field','table.field' ...
     * by default "a.*" ,"b.*"
     * @param string ...$fields
     * @return $this
     */
    public function select(string ...$fields){
        $this->query->select(...$fields);
        return $this;
    }

    /**
     * @param $src_array
     * @param $alias
     * @return $this
     */
    public function from($src_array, $alias){
        $this->query->from($src_array, $alias);
        return $this;
    }

    /**
     * @param $join_array
     * @param $alias
     * @return $this
     */
    public function join($join_array, $alias){
        $this->query->join($join_array, $alias);
        return $this;
    }

    /**
     * @param $join_array
     * @param $alias
     * @return $this
     */
    public function leftJoin($join_array, $alias){
        $this->query->leftJoin($join_array, $alias);
        return $this;
    }

    /**
     * @param $join_array
     * @param $alias
     * @return $this
     */
    public function rightJoin($join_array, $alias){
        $this->query->rightJoin($join_array, $alias);
        return $this;
    }

    /**
     * @param $join_array
     * @param $alias
     * @return $this
     */
    public function innerJoin($join_array, $alias){
        $this->query->innerJoin($join_array, $alias);
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
        $this->query->on(...$ons);
        return $this;
    }

    /**
     * @param ...$on
     */
    public function orOn(...$on){
        $this->query->orOn(...$on);
        return $this;
    }

    /**
     * @return mixed
     */
    public function onBrackets(){
        return $this->query->onBrackets();
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
        $this->query->where(...$wheres);
        return $this;
    }

    /**
     * @return Components\Wheres
     */
    public function whereBrackets(){
        return $this->query->whereBrackets();
    }

    /**
     * @param $where
     */
    public function whereIn($where){
        $this->query->whereIn($where);
    }

    /**
     * @param $where
     */
    public function whereNotIn($where){
        $this->query->whereNotIn($where);
    }

    /***
     * @param $where
     */
    public function orWhereNotIn($where){
        $this->query->orWhereNotIn($where);
    }

    /**
     * @param $where
     */
    public function orWhereIn($where){
        $this->query->orWhereIn($where);
    }

    /**
     * @param $where
     */
    public function orWhere($where){
        $this->query->orWhere($where);
    }

    /**
     * @param $where
     */
    public function whereBetween($where){
        $this->query->whereBetween($where);
    }

    /**
     * @param $where
     */
    public function orWhereBetween($where){
        $this->query->whereBetween($where);
    }

    /**
     * @desc The format of arguments is ['table.field','asc'] ,['table.field','asc']  ...
     * @param array ...$order_bys
     * @return $this
     */
    public function orderBy(array ...$order_bys){
        return $this->query->orderBy(...$order_bys);
    }

    /**
     * @param string ...$group_bys
     * @return $this
     */
    public function groupBy(string ...$group_bys ){
        return $this->query->groupBy(...$group_bys);
    }

    /**
     * @param Closure|null $closure
     * @return QueryList
     */
    public function execute(Closure $closure = null){
        $this->query->execute();
        return $this->_makeJoin($closure);
    }

    /**
     * @param Closure|null $closure
     * @return QueryList
     */
    public function get(Closure $closure = null){
        $this->query->get();
        return $this->_makeJoin($closure);
    }

    /**
     * @desc For debug, you could check the sql.
     * @return string
     */
    public function toSql(){
        return $this->query->toSql();
    }

    /**
     * 传入 "key1.key2.key3" 进行搜索
     * @param $pathString
     * @return bool
     */
    public function findPath($pathString){
        return $this->query->findPath($pathString);
    }
    /**
     * @param $alias
     * @param $field
     * @return array
     */
    public function pluck($alias,$field){
        return $this->query->pluck($alias,$field);
    }

    /**
     * @param $alias
     * @param $field
     * @return array
     */
    public function getForeignKeyValues($alias,$field){
        return $this->query->pluck($alias,$field);
    }

    /**
     *
     */
    private function _makeJoin(){

    }
}