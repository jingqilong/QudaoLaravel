<?php


namespace App\Library\ArrayModel;


/**
 * Class Criteria
 * @package App\Library\ArrayModel
 */
class Criteria extends Tree
{
    use CriteriaTrait;

    const NODE_TYPE_EQUATION   = 0;

    const NODE_TYPE_AGGREGATE  = 1;

    /**
     * _node_type
     *
     * @var int
     */
    public $_node_type = 0;

    /**
     * _logic
     *
     * @var null
     */
    public $_logic = null;

    /**
     * _alias
     *
     * @var string
     */
    public $_alias  = '';

    /**
     * _field
     *
     * @var string
     */
    public $_field  = '';

    /**
     * _operator
     *
     * @var string
     */
    public $_operator  = '';

    /**
     * _criteria_value
     *
     * @var string
     */
    public $_criteria_value  = '';


    /**
     * _children
     *
     * @var array|Criteria
     */
    public $_children = [];

    /**
     * @param int $level
     * @return string
     */
    public function _toSql($level=0){
        $result = "";
        if(self::NODE_TYPE_AGGREGATE == $this->_node_type){
            $result .= "(";
            foreach($this->_children as $node){
                $result .= $node->_toSql($level+1);
            }
            $result .= ")";
            return $result;
        }
        $result .= "(";
        $criteria_value = is_numeric($this->_criteria_value)
            ? " " . $this->_criteria_value ." "
            :" '" . $this->_criteria_value . "' ";
        $operator = $this->getOperator($this->_operator);
        $result .= $this->_alias . "." . $this->_field . $operator .$criteria_value;
        $result .= ")";
        return $result;
    }


}