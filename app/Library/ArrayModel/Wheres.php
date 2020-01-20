<?php


namespace App\Library\ArrayModel;


/**
 * Class Wheres
 * @package App\Library\ArrayModel
 */
class Wheres extends Criteria
{

    /**
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
     * @param $cur_values
     * @param int $level
     * @return mixed|null
     */
    public function _getValue($cur_values,$level = 0){
        $result = null;
        if(self::NODE_TYPE_AGGREGATE == $this->_node_type){
            foreach ($this->_children as $node) {
                $value = $node->_getValue($level + 1);
                if (null !== $result) {
                    $func = $node->_logic;
                    $result = $this->$func($result, $value);
                } else {
                    $result = $value;
                }
            }
            return $result;
        }
        $value = $cur_values[$this->_alias][$this->_field];
        $result = $this->callByName($value,$this->_criteria_value,$level);
        return $result;
    }



    /**
     * @param $where
     * @param $operator
     * @param $logic
     * @param int $level
     */
    public function _addWhere($where,$operator=null,$logic='',$level=0){
        if(0==$level){
            $node = Wheres::of();
            $node->_addWhere($where,$operator,$logic,$level+1);
            $this->_children[]=$node;
        }
        $this->_node_type = self::NODE_TYPE_EQUATION;
        if(is_array($where)){
            if(2==count($where)){
                list($column,$value) = $where;
                if(empty($operator)){
                    $operator = 'eq';
                }
            }else{
                list($column,$operator,$value) = $where;
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
     * @param $wheres
     * @param $inner_logic
     * @param $group_logic
     * @param int $level
     */
    public function _addWheres($wheres,$inner_logic,$group_logic,$level=0){
        $node = Wheres::of();
        $node->_logic = $group_logic;
        $this->_children[]=$node;
        $logic = ['',$inner_logic]; $i=0;
        foreach($wheres as $where){
            $node->addWhere($where,null,$logic[$i],$level+1);
        }
    }
}