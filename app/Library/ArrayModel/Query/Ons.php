<?php

namespace App\Library\ArrayModel\Query;

use App\Library\ArrayModel\LogicTree\BracketsNode;
use App\Library\ArrayModel\LogicTree\TreeConstants;
use Closure;

/**
 * Class Ons
 * @package App\Library\ArrayModel\Query
 */
class Ons extends BracketsNode
{

    /**
     * create a empty instance of Ons
     *
     * @return Ons
     */
    public static function of(){
        $instance = new static();
        return $instance;
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
        $result .= parent::_toSql($level);
        return $result;
    }

    /**
     * Could pass any number of on-conditions and Closures
     *
     * @param array|Closure ...$ons
     * @return $this
     */
    public function on(...$ons){
        $logic = "and"; $i=0;
        foreach($ons as $on){
            if($on instanceof Closure){
                $group = $this->onBrackets();
                $on->bindTo($group);
                $on($group);
                continue;
            }
            $this->_addOn($on,'',$logic[$i]);
            $i=1;
        }
        return $this;
    }

    /**
     * using the brackets "()" to change the logic calculating.
     *
     * @return Ons
     */
    public function onBrackets(){
        $new_on = self::of();
        $new_on->_node_type = TreeConstants::NODE_TYPE_AGGREGATE;
        $this->_children = $new_on;
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
     * Add the on-condition to the class
     *
     * @param $on
     * @param $operator
     * @param $logic
     * @param int $level
     */
    public function _addOn($on,$operator=null,$logic='',$level=0){
        if(0==$level){
            $node = Ons::of();
            $node->_node_type = TreeConstants::NODE_TYPE_AGGREGATE;
            $node->_addOn($on,$operator,$logic,$level+1);
            $this->_children[]=$node;
        }
        $this->_node_type = TreeConstants::NODE_TYPE_EXPRESSION;
        if(is_array($on)){
            if(2==count($on)){
                list($column,$value) = $on;
                if(empty($operator)){
                    $operator = 'eq';
                }
            }else{
                list($column,$operator,$value) = $on;
                $operator = $this->getOperatorName($operator);
            }
            $this->_logic = $logic;
            $field_set = explode('.',$column);
            list($alias,$field) = $field_set;
            $this->_alias = $alias;
            $this->_field = $field;
            $this->_operator = $operator;
            $this->_criteria_value = $value;
        }
    }

    /**
     * get the foreign keys from the on-conditions.
     *
     * @param null $keys
     * @param int $level
     * @return array|null
     */
    public function getForeignKeys(&$keys=null,$level=0){
        if(null == $keys ){
            $keys = [];
        }
        foreach($this->_children as $node){
            if($node instanceof Ons)
                $node->getForeignKeys($keys,$level+1);
        }
        if($this->_node_type = TreeConstants::NODE_TYPE_EXPRESSION) {
            $keys[] = $this->_field;
        }
        return $keys;
    }

    /**
     * get the local keys from the on-conditions.
     *
     * @param null $keys
     * @param int $level
     * @return array|null
     */
    public function getLocalKeys(&$keys=null,$level=0){
        if(null == $keys ){
            $keys = [];
        }
        foreach($this->_children as $node){
            if($node instanceof Ons)
                $node->getLocalKeys($keys,$level+1);
        }
        if($this->_node_type = TreeConstants::NODE_TYPE_EXPRESSION){
            $keys[]=$this->_criteria_value;
        }

        return $keys;
    }

}