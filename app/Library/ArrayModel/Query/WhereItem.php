<?php
/**
 * Created by PhpStorm.
 * User: Bardeen
 * Date: 2020-01-30
 * Time: 22:52
 */

namespace App\Library\ArrayModel\Query;

use App\Library\ArrayModel\LogicTree\ExpressionNode;
use App\Library\ArrayModel\LogicTree\NodeInterface;
use Closure;

class WhereItem extends ExpressionNode
{

    /**
     * @return Wheres|NodeInterface|null
     */
    public function getBracketsNode()
    {
        return $this->brackets_node;
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
        return $this->getBracketsNode()->where(...$wheres);
    }

    /**
     * @return Wheres
     */
    public function whereBrackets(){
        return $this->getBracketsNode()->whereBrackets();
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereIn($where){
        return $this->getBracketsNode()->whereIn($where);
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereNotIn($where){
        return $this->getBracketsNode()->whereNotIn($where);
    }

    /***
     * @param $where
     * @return $this
     */
    public function orWhereNotIn($where){
        return $this->getBracketsNode()->orWhereNotIn($where);
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereIn($where){
        return $this->getBracketsNode()->orWhereIn($where);
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhere($where){
        return $this->getBracketsNode()->orWhere($where);
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereBetween($where){
        return $this->getBracketsNode()->whereBetween($where);
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereBetween($where){
        return $this->getBracketsNode()->orWhereBetween($where);
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereIs($where){
        return $this->getBracketsNode()->whereIs($where);
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereIs($where){
        return $this->getBracketsNode()->orWhereIs($where);
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereIsNot($where){
        return $this->getBracketsNode()->whereIsNot($where);
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereIsNot($where){
        return $this->getBracketsNode()->orWhereIsNot($where);
    }

    /**
     * calculate the value of the where-conditions.
     *
     * @param $cur_values
     * @param int $level
     * @return mixed|null
     */
    public function _getValue($cur_values,$level = 0){
        return parent::_getValue($cur_values,$level);
    }

}