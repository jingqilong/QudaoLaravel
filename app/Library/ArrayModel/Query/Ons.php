<?php

namespace App\Library\ArrayModel\Query;

use App\Library\ArrayModel\LogicTree\BracketsNode;
use App\Library\ArrayModel\LogicTree\ExpressionNode;
use App\Library\ArrayModel\LogicTree\TreeConstants;
use App\Library\ArrayModel\LogicTree\NodeInterface;
use Closure;

/**
 * Class Ons
 * @package App\Library\ArrayModel\Query
 */
class Ons extends BracketsNode
{
    /**
     * @param array $expression
     * @param string $logic
     * @param string $operator
     * @return ExpressionNode
     */
    public static function newNode($expression,$logic=TreeConstants::LOGIC_AND, $operator='='){
        return new static($expression,$logic,$operator);
    }

    /**
     * @param string $logic
     * @return Wheres|NodeInterface
     */
    public static function newBracketsNode($logic = TreeConstants::LOGIC_AND){
        $new_node = new Ons();
        return $new_node->setLogic($logic);
    }

    /**
     * @param $expression
     * @return array
     */
    public function expressionToArray($expression){
        $_operator = '=';
        $field_alias = $_field_join = "";
        if(2 == count($expression)){
            list($field_alias,$_field_join) = $expression;
        }elseif(3 == count($expression)){
            list($field_alias,$_operator,$_field_join) = $expression;
        }
        list($_alias,$_field) = explode(".",$field_alias);
        list($_alias_join,$_field_join) = explode(".",$_field_join);
        $_operator = $this->getOperatorName($_operator);
        return compact($_alias,$_field,$_operator,$_alias_join,$_field_join);
    }

    /**
     * @return bool
     */
    public function reduce(){
        return $this->reduceLogic();
    }

    /**
     * For debugging, import the sql
     *
     * @param int $level
     * @return string
     */
    public function toSql($level = 0){
        $result = '';
        if (0 == $level )
            $result .= "ON ";
        if(!empty($this->_logic))
            $result .= " " .$this->_logic . " ";
        $result .= parent::_toSql();
        return $result;
    }

    /**
     * Could pass any number of on-conditions and Closures
     *
     * @param array|Closure ...$ons
     * @return $this
     */
    public function on(...$ons){
        $logic = TreeConstants::LOGIC_AND;
        foreach($ons as $on){
            if($on instanceof Closure){
                $group = $this->onBrackets();
                $on->bindTo($group);
                $on($group);
                continue;
            }
            $this->_addOn($on,null,$logic);
        }
        return $this;
    }

    /**
     * using the brackets "()" to change the logic calculating.
     *
     * @return Ons
     */
    public function onBrackets(){
        $new_on = static::newBracketsNode(TreeConstants::LOGIC_AND);
        $this->addNext($new_on);
        return $new_on;
    }

    /**
     * Add a on-condition with logic operator or
     *
     * @param $on
     * @return $this
     */
    public function orOn($on){
        $this->_addOn($on,"","or");
        return $this;
    }

    /**
     * Add a on-condition with logic operator and
     *
     * @param $on
     * @return $this
     */
    public function andOn($on){
        $this->_addOn($on,"","and");
        return $this;
    }

    /**
     * Add the on-condition to the class
     *
     * @param $on
     * @param $operator
     * @param $logic
     */
    protected function _addOn($on,$operator=null,$logic=TreeConstants::LOGIC_AND){
        $new_node = static::newNode($on,$logic,$operator);
        $this->_addNode($new_node);
    }

    /**
     * @param $keys
     * @param $logic
     */
    protected function getForeignKeysLogic(&$keys,$logic){
        $children = $this->getChildren($logic);
        /** @var  $child Ons|OnItem */
        foreach($children->items() as $child){
            $child->getForeignKeys($keys);
        }
    }

    /**
     * get the foreign keys from the on-conditions.
     *
     * @param array $keys
     * @return array|null
     */
    public function getForeignKeys(&$keys=[]){
        $this->getForeignKeysLogic($keys,TreeConstants::LOGIC_AND);
        $this->getForeignKeysLogic($keys,TreeConstants::LOGIC_OR);
        return $keys;
    }

    /**
     * @param $keys
     * @param $logic
     */
    protected function getLocalKeysLogic(&$keys,$logic){
        $children = $this->getChildren($logic);
        /** @var  $child Ons|OnItem */
        foreach($children->items() as $child){
            $child->getLocalKeys($keys);
        }
    }

    /**
     * get the local keys from the on-conditions.
     *
     * @param array $keys
     * @return array|null
     */
    public function getLocalKeys(&$keys=[]){
        $this->getForeignKeysLogic($keys,TreeConstants::LOGIC_AND);
        $this->getForeignKeysLogic($keys,TreeConstants::LOGIC_OR);
        return $keys;
    }

}