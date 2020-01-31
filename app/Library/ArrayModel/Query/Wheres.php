<?php

namespace App\Library\ArrayModel\Query;

use App\Library\ArrayModel\LogicTree\TreeConstants;
use App\Library\ArrayModel\LogicTree\BracketsNode;
use App\Library\ArrayModel\LogicTree\ExpressionNode;
use App\Library\ArrayModel\LogicTree\NodeInterface;
use App\Library\ArrayModel\LogicTree\Node;
use Closure;

/**
 * Class Wheres
 * @package App\Library\ArrayModel\Query
 */
class Wheres extends BracketsNode
{
    /**
     * @param array $expression
     * @param null $logic
     * @param null $operator
     * @return ExpressionNode
     */
    public function newNode($expression,$logic,$operator=null){
        return parent::newNode($expression,$logic,$operator);
    }

    /**
     * @param int $logic
     * @return BracketsNode|NodeInterface
     */
    public function newBracketsNode($logic){
        return parent::newBracketsNode($logic);
    }

    /**
     * @param NodeInterface|Node $node
     * @return NodeInterface|Node|bool
     */
    public function _addNode(NodeInterface $node){
        return parent::_addNode($node);
    }

    /**
     * @param NodeInterface|Node $node
     * @return NodeInterface|Node|bool
     */
    public function _addBracketsNode(NodeInterface $node){
        return parent::_addBracketsNode($node);
    }

    /**
     * @return bool
     */
    public function reduce(){
        return $this->reduceLogic();
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
        $logic = TreeConstants::LOGIC_AND;
        foreach($wheres as $where){
            if($where instanceof Closure){
                $group = $this->whereBrackets();
                $where->bindTo($group);
                $where($group);
                continue;
            }
            $this->_addWhere($where,null,$logic);
        }
        return $this;
    }

    /**
     * @return Wheres
     */
    public function whereBrackets(){
        $new_where = $this->newBracketsNode(TreeConstants::LOGIC_AND);
        $this->addNext($new_where);
        return $new_where;
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereIn($where){
        $this->_addWhere($where,'in','and');
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereNotIn($where){
        $this->_addWhere($where,'notIn','and');
        return $this;
    }

    /***
     * @param $where
     * @return $this
     */
    public function orWhereNotIn($where){
        $this->_addWhere($where,'notIn','or');
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereIn($where){
        $this->_addWhere($where,'in','or');
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhere($where){
        $this->_addWhere($where,'','or');
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereBetween($where){
        $wheres = [];
        list($field,$min,$max) = $where;
        $wheres[] = [$field,'>=',$min];
        $wheres[] = [$field,'<=',$max];
        $this->_addWheres($wheres,'and','and');
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereBetween($where){
        $wheres = [];
        list($field,$min,$max) = $where;
        $wheres[] = [$field,'>=',$min];
        $wheres[] = [$field,'<=',$max];
        $this->_addWheres($wheres,'and','or');
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereIs($where){
        $this->_addWhere($where,'is','and');
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereIs($where){
        $this->_addWhere($where,'is','or');
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereIsNot($where){
        $this->_addWhere($where,'isNot','and');
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereIsNot($where){
        $this->_addWhere($where,'isNot','or');
        return $this;
    }

    /**
     * For debugging, import sql
     *
     * @param int $level
     * @return string
     */
    public function toSql($level = 0){
        $result = '';
        if (0 == $level )
            $result .= "WHERE ";
        if(!empty($this->_logic))
            $result .= " " .$this->_logic . " ";
        $result .= parent::_toSql($level);
        return $result;
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

    /**
     * add where-condition
     *
     * @param array|Closure $where
     * @param $operator
     * @param $logic
     */
    public function _addWhere($where,$operator=null,$logic=TreeConstants::LOGIC_AND){
        $new_node = $this->newNode($where,$logic,$operator);
        $this->_addNode($new_node);
    }

    /**
     * add some where-conditions.
     *
     * @param $wheres
     * @param $inner_logic
     * @param $group_logic
     * @param int $level
     */
    public function _addWheres($wheres,$inner_logic,$group_logic,$level=0){
        $node = $this->newBracketsNode($group_logic);
        foreach($wheres as $where){
            $node->_addWhere($where,null,$inner_logic,$level+1);
        }
    }
}