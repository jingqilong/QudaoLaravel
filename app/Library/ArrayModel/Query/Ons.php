<?php

namespace App\Library\ArrayModel\Query;

use App\Library\ArrayModel\LogicTree\BracketsNode;
use App\Library\ArrayModel\LogicTree\TreeConstants;
use Closure;

/**
 * Class Ons
 * @package App\Library\ArrayModel\Query
 * @author Bardeen
 */
class Ons extends BracketsNode
{
    /**
     * @var bool
     */
    protected $_is_contains = false;

    /**
     * @param $bracketsNode
     * @param $expression
     * @param string $logic
     * @param string $operator
     * @return Onitem
     * @static
     */
    public static function newNode($bracketsNode,$expression,$logic=TreeConstants::LOGIC_AND, $operator='='){
        return new Onitem($bracketsNode,$expression,$logic,$operator);
    }

    /**
     * @param $bracketsNode
     * @param string $logic
     * @return Ons
     * @static
     */
    public static function newBracketsNode($bracketsNode,$logic = TreeConstants::LOGIC_AND){
        return new Ons($bracketsNode,$logic);
    }

    /**
     * @param $value
     */
    public function setContains($value){
        $this->_is_contains = $value;
    }

    /**
     * @return bool
     */
    public function isContains(){
        return $this->_is_contains;
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
                $group = static::newBracketsNode($logic);
                $on->bindTo($group);
                $on($group);
                $this->addNext($group);
                continue;
            }
            $this->_addOn($on,null,$logic);
        }
        return $this;
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
     * @param $on
     * @return $this
     */
    public function onContains($on){
        $this->_addOn($on,"contains","and");
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
        $new_node = static::newNode($this,$on,$logic,$operator);
        $this->_addNode($new_node);
    }

    /**
     * @param int $key_type
     * @param int $level
     * @return array
     */
    public function getOnsKeys($key_type=TreeConstants::KEY_FORIEGN,$level=0){
        $ons_and =$this->getOnsKeysOfAnd($key_type,$level);
        $ons_or = $this->getOnsKeysOfOr($key_type,$level);
        $ons = array_merge($ons_and,$ons_or);
        return $ons;
    }

    /**
     * @param $key_type
     * @param int $level
     * @return array
     */
    public function getOnsKeysOfOr($key_type,$level=0){
        $logic = TreeConstants::LOGIC_OR;
        $children = $this->getChildren($logic);
        $ons = [];
        /** @var  $child Ons|OnItem */
        foreach($children->items() as $child){
            $ons[] = $child->getOnsKeys($key_type,$level+1);
        }
        return $ons;
    }

    /**
     * @param $key_type
     * @param int $level
     * @return array
     */
    public function getOnsKeysOfAnd($key_type,$level=0){
        $logic = TreeConstants::LOGIC_AND;
        $children = $this->getChildren($logic);
        /** @var  $child Ons|OnItem */
        $child = $children->getFirst();
        $ons = $child->getOnsKeys($key_type,++$level);
        if($child->hasNext()){
            /** @var  $next Ons|OnItem */
            $next = $child->getNext();
            $ons['children'] = $next->getOnsKeys($key_type,++$level);
        }
        return $ons;
    }

    /**
     * @return bool
     */
    public function reduce(){
        return $this->reduceLogic();
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

}