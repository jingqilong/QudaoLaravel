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
     * Node constructor.
     * @param array $expression
     * @param string $logic
     * @param string $operator
     */
    public function __construct($expression=[],$logic=TreeConstants::LOGIC_AND,$operator='='){
        if(!empty($expression)){
            $properties = $this->expressionToArray($expression);
            foreach($properties as $key => $value){
                $this->$key = $value;
            }
        }
        $this->setLogic($logic);
        $this->setOperator($this->getOperatorName($operator));
        parent::__construct();
    }

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
     * @param string $logic
     * @param string $operator
     * @return ExpressionNode
     */
    public static function newNode($expression,$logic=TreeConstants::LOGIC_AND, $operator='='){
        return new static($expression,$logic,$operator);
    }

    /**
     * @param int $logic
     * @return NodeInterface
     */
    public static function newBracketsNode($logic){
        return BracketsNode::newBracketsNode($logic);
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