<?php


namespace App\Library\ArrayModel;

/**
 * Class Wheres
 * @package App\Library\ArrayModel
 */
class Wheres extends CriteriaNode
{
    /**
     * @param $main_item
     * @param $main_alias
     * @param $join_item
     * @param $join_alias
     * @return mixed
     */
    public function _checkCriteria($main_item,$main_alias, $join_item, $join_alias){
        if("" == $this->_field){
            $values = [];
            foreach($this->_children as $node){
                $values = $node->_checkCriteria($main_item,$main_alias, $join_item, $join_alias);
            }
            return $this->callByName($values[0],$values[1]);
        }
        if(isset($this->_field[$main_alias])){
            $value = $main_item[$this->_field[$main_alias]];
            return $this->callByName($value,$this->_criteria_value);
        }
    }

    /**
     * @param $value1
     * @param $value2
     * @return mixed
     */
    private function callByName($value1,$value2){
        $logic_func = $this->_logic;
        $func = $this->_operator;
        $result =  $this->$func($value1,$value2);
        if("" == $logic_func)
            return $result;
        return $this->$logic_func($result);
    }
}