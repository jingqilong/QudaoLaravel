<?php


namespace App\Library\ArrayModel;

/**
 * Class Ons
 * @package App\Library\ArrayModel
 */
class Ons extends Criteria
{

    /**
     * @return Ons
     */
    public static function of(){
        $instance = new static();
        return $instance;
    }
    /**
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
     * @param $on
     * @param $operator
     * @param $logic
     * @param int $level
     */
    public function _addOn($on,$operator=null,$logic='',$level=0){
        if(0==$level){
            $node = Ons::of();
            $node->_addOn($on,$operator,$logic,$level+1);
            $this->_children[]=$node;
        }
        $this->_node_type = self::NODE_TYPE_EQUATION;
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
        $keys[]=$this->_field;
        return $keys;
    }

    /**
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
        $keys[]=$this->_criteria_value;
        return $keys;
    }
}