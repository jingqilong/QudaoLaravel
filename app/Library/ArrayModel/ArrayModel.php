<?php
/**
 * Created by PhpStorm.
 * User: Bardeen
 * Date: 2020-01-21
 * Time: 21:00
 */

namespace App\Library\ArrayModel;

use App\Library\ArrayModel\Query\QueryBuilder;
use Closure;
use BadMethodCallException;

/**
 * Class ArrayModel
 * @package App\Library\ArrayModel
 */
class ArrayModel
{
    /**
     * @var QueryBuilder
     */
    public $query;

    /**
     * @var QueryBuilder
     */
    public $result;

    /**
     * ArrayModel constructor.
     */
    public function __construct()
    {
        $this->query = QueryBuilder::of();
        $this->result = QueryBuilder::of();
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
     * @param string[] ...$fields
     * @return $this
     */
    public function select(...$fields){
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
     * @param $on
     * @return $this
     */
    public function orOn($on){
        $this->query->orOn($on);
        return $this;
    }

    /**
     * @param $on
     * @return $this
     */
    public function andOn($on){
        $this->query->andOn($on);
        return $this;
    }

    /**
     * @param $on
     * @return $this
     */
    public function onContains($on){
        $this->query->onContains($on);
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
     * @param $where
     * @return $this
     */
    public function orWhere($where){
        $this->query->orWhere($where);
        return $this;
    }

    /**
     * @return Query\Wheres
     */
    public function whereBrackets(){
        return $this->query->whereBrackets();
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereIn($where){
        $this->query->whereIn($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereNotIn($where){
        $this->query->whereNotIn($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereNotIn($where){
        $this->query->orWhereNotIn($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereIn($where){
        $this->query->orWhereIn($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereBetween($where){
        $this->query->whereBetween($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereBetween($where){
        $this->query->whereBetween($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereIs($where){
        $this->query->whereIs($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereIs($where){
        $this->query->orWhereIs($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereIsNot($where){
        $this->query->whereIsNot($where);
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereIsNot($where){
        $this->query->orWhereIsNot($where);
        return $this;
    }

    /**
     * @desc The format of arguments is ['table.field','asc'] ,['table.field','asc']  ...
     * @param string[] ...$order_bys
     * @return $this
     */
    public function orderBy(...$order_bys){
        return $this->query->orderBy(...$order_bys);
    }

    /**
     * @param string[] ...$group_bys
     * @return $this
     */
    public function groupBy(...$group_bys ){
        return $this->query->groupBy(...$group_bys);
    }

    /**
     * @param Closure|null $closure
     * @return QueryBuilder
     */
    public function execute(Closure $closure = null){
        return $this->query->execute($closure);
    }

    /**
     * @param Closure|null $closure
     * @return QueryBuilder
     */
    public function get(Closure $closure = null){
        return $this->query->get($closure);
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
     * Find the row with specified condition array which is dot-separated string.
     *
     * @param $conditions
     * @return bool
     */
    public function findByConditions($conditions){
        return $this->query->findByConditions($conditions);
    }

    /**
     * Find the row with specified keys which is dot-separated string.
     *
     * @param $keys
     * @return bool
     */
    public function findByKeys($keys){
        return $this->query->findByKeys($keys);
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

}