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
    public $_value  = '';


    public $node_type = TreeConstants::NODE_TYPE_EXPRESSION;

    /**
     * @var null
     */
    public $node_logic = null;

    /**
     * properties : Keep the properties of node
     *
     * @var array
     */
    public $properties = [];


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
     * @return ExpressionNode
     */
    public function newNode($expression,$logic){
        $node = parent::newNode($expression,$logic);
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
     * @param NodeInterface|Node $node
     * @return NodeInterface|Node|bool
     */
    public function _addNode(NodeInterface $node){
        return $this->getBracketsNode()->_addNode($node);
    }

    /**
     * @param NodeInterface|Node $node
     * @return NodeInterface|Node|bool
     */
    public function _addBracketsNode(NodeInterface $node){
        return $this->getBracketsNode()->_addNode($node);
    }

    /**
     * @param NodeInterface|Node $node
     * @return $this|NodeInterface|Node
     */
    public function _addLeftNode(NodeInterface $node){
        return $this->getBracketsNode()->_addLeftNode($node);
    }

    /**
     * @param NodeInterface|Node $node
     * @return $this|NodeInterface|Node
     */
    public function _addAndNode(NodeInterface $node){
        return $this->getBracketsNode()->_addAndNode($node);
    }

    /**
     * @param NodeInterface|Node $node
     * @return $this|NodeInterface|Node
     */
    public function _addOrNode(NodeInterface $node){
        return $this->getBracketsNode()->_addOrNode($node);
    }
    /**
     * @param NodeInterface|Node $node
     * @return NodeInterface|Node
     */
    public function _addLeftBracketsNode(NodeInterface $node){
        return $this->getBracketsNode()->_addLeftBracketsNode($node);
    }

    /**
     * @param NodeInterface|Node $node
     * @return NodeInterface|Node
     */
    public function _addAndBracketsNode(NodeInterface $node){
        return $this->getBracketsNode()->_addAndBracketsNode($node);
    }

    /**
     * @param NodeInterface|Node $node
     * @return NodeInterface|Node
     */
    public function _addOrBracketsNode(NodeInterface $node){
        return $this->getBracketsNode()->_addOrBracketsNode($node);
    }

    /**
     * @param NodeInterface|Node $nodes
     * @return NodeInterface|Node
     */
    public function _addAndNodes(NodeInterface $nodes){
        return $this->getBracketsNode()->_addAndNodes($nodes);
    }

    /**
     * @param NodeInterface|Node $nodes
     * @return NodeInterface|Node
     */
    public function _addOrNodes(NodeInterface $nodes){
        return $this->getBracketsNode()->_addOrNodes($nodes);
    }

    /**
     * @param array $expression
     * @param string $logic
     * @return mixed
     */
    public function addNode($expression=[],$logic=''){
        return $this->getBracketsNode()->addNode($expression,$logic);
    }

    /**
     * @param array $expression
     * @return NodeInterface
     */
    public function addAndNode($expression=[]){
        return $this->getBracketsNode()->addAndNode($expression);
    }

    /**
     * @param array $expression
     * @return NodeInterface
     */
    public function addOrNode($expression=[]){
        return $this->getBracketsNode()->addOrNode($expression);
    }

    /**
     * @param ...$expression
     * @return $this
     */
    public function addAndNodes(...$expression){
        return $this->getBracketsNode()->addAndNodes(...$expression);
    }

    /**
     * @param ...$expression
     * @return $this
     */
    public function addOrNodes(...$expression){
        return $this->getBracketsNode()->addOrNodes(...$expression);
    }

    /**
     * @param $logic
     * @return NodeInterface
     */
    public function addBracketsNode($logic){
        return $this->getBracketsNode()->addBracketsNode($logic);
    }

    /**
     * @return NodeInterface
     */
    public function addAndBracketsNode(){
        return $this->getBracketsNode()->addAndBracketsNode();
    }

    /**
     * @return NodeInterface
     */
    public function addOrBracketsNode(){
        return $this->getBracketsNode()->addOrBracketsNode();
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
        $result = null;
        $value = $cur_values[$this->_field];
        $result = $this->callByName($value,$this->_value,$level);
        return $result;
    }

}