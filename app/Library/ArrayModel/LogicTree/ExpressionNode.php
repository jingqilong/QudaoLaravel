<?php
/**
 * Created by PhpStorm.
 * User: Bardeen
 * Date: 2020-01-26
 * Time: 2:19
 */

namespace App\Library\ArrayModel\LogicTree;

/**
 * Class ExpressionNode
 * @package App\Library\ArrayModel\LogicTree
 */
class ExpressionNode extends Node implements NodeInterface
{

    use NodeTrait;

    /**
     * _alias
     *
     * @var string
     */
    protected $_alias  = '';

    /**
     * _field
     *
     * @var string
     */
    protected $_field  = '';

    /**
     * _operator
     *
     * @var string
     */
    protected $_operator  = '';

    /**
     * _criteria_value
     *
     * @var string
     */
    protected $_value  = '';

    /**
     * @var int
     */
    protected $node_type = TreeConstants::NODE_TYPE_EXPRESSION;

    /**
     * @var null
     */
    protected $node_logic = null;

    /**
     * properties : Keep the properties of node
     *
     * @var array
     */
    protected $properties = [];

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->properties[$name];
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->properties[$name] = $value;
    }

    /**
     * @param $operator
     * @return $this
     */
    public function setOperator($operator){
        $operator_name = $this->getOperatorName($operator);
        $this->_operator = $operator_name;
        return $this;
    }

    /**
     * @param array $expression
     * @param null $logic
     * @param null $operator
     * @return ExpressionNode
     */
    public function newNode($expression,$logic, $operator=null){
        $node = parent::newNode($expression,$logic,$operator);
        return $node;
    }

    /**
     * @param int $logic
     * @return NodeInterface
     */
    public function newBracketsNode($logic){
        return parent::newBracketsNode($logic);
    }

    /**
     * @param NodeInterface|Node $node
     * @return NodeInterface
     */
    public function addNext(NodeInterface $node){
        return $this->getBracketsNode()->addNext($node);
    }

    /**
     * @param int $level
     * @return string
     */
    public function _toSql($level=0){
        $result = "";
        if (false === $this->getFirstFlag()) {
            $result .= $this->getLogicString();
        }
        $result .= "(";
        $_value = is_numeric($this->_value)
            ? " " . $this->_value ." "
            :" '" . $this->_value . "' ";
        $operator = $this->getOperator($this->_operator);
        $result .= $this->_alias . "." . $this->_field . $operator .$_value;
        $result .= ")";
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
        $value = $cur_values[$this->_field];
        $result = $this->callByName($value,$this->_value,$level);
        return $result;
    }

}