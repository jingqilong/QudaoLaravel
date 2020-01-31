<?php
/**
 * Created by PhpStorm.
 * User: Bardeen
 * Date: 2020-01-26
 * Time: 2:15
 */

namespace App\Library\ArrayModel\LogicTree;

/**
 * Class Node
 * @package App\Library\ArrayModel\LogicTree
 */
abstract class Node
{
    use NodeTrait;

    /**
     * @var null
     */
    protected $node_logic = null;

    /**
     * parent: Reference to the parent node.
     *
     * @var null
     */
    protected $parent = null;

    /**
     * result_data : keep the array of data after the logic calculate.
     *
     *
     * @var array
     */
    protected $result_data = [];

    /**
     * brackets_node: Keep the brackets node of this node
     *
     * @var BracketsNode|null
     */
    protected $brackets_node = null;

    /**
     * prev_node: Keep the previous node of this node
     *
     * @var NodeInterface|null
     */
    protected $prev_node = null;

    /**
     * next_node: Keep the next node of this node
     *
     * @var NodeInterface|null
     */
    protected $next_node = null;

    /**
     * @var null
     */
    protected $node_type = null;


    /**
     * @var bool
     */
    protected $first_flag = false;

    /**
     * Node constructor.
     */
    public function __construct(){}

    /**
     * @return null
     */
    public function getNodeType(){
        return $this->node_type;
    }

    /**
     * @return null|string
     */
    public function getLogic(){
        return $this->node_logic;
    }

    /**
     * @param string $logic
     * @return $this
     */
    public function setLogic($logic){
        $this->node_logic = $logic;
        return $this;
    }

    /**
     * @param array $expression
     * @param null $logic
     * @return ExpressionNode
     */
    /**
     * @param array $expression
     * @param null $logic
     * @param null $operator
     * @return $this
     */
    public function newNode($expression=[],$logic = null, $operator=null){
        if(null === $logic){
            $logic = TreeConstants::LOGIC_AND;
        }
        $new_node = new ExpressionNode();
        $node_data = $this->expressionToArray($expression);
        foreach($node_data as $key =>$value){
            $new_node->$key = $value;
        }
        if(null!==$operator){
            $new_node->setOperator($operator);
        }
        return $new_node->setLogic($logic);
    }

    /**
     * @param $logic
     * @return BracketsNode|NodeInterface
     *
     */
    public function newBracketsNode($logic = null){
        if(null === $logic){
            $logic = TreeConstants::LOGIC_AND;
        }
        $new_node = new BracketsNode();
        return $new_node->setLogic($logic);
    }

    /**
     * @param $node
     * @return $this
     */
    public function setParent($node){
        $this->parent = $node;
        return $this;
    }

    /**
     * @return Node
     */
    public function getParent(){
        return $this->parent;
    }

    /**
     * @return $this|Node
     */
    public function getAncestor(){
        return $this->getParent()->getAncestor();
    }

    /**
     * @param $node
     */
    public function setPrev($node){
        $this->prev_node = $node;
    }

    /**
     * @return NodeInterface|null
     */
    public function getPrev(){
        return $this->prev_node;
    }

    /**
     * @return bool
     */
    public function hasPrev(){
        if(TreeConstants::LOGIC_LEFT === $this->node_logic){
            return false;
        }
        return (null !== $this->prev_node);
    }

    /**
     * @param $node
     * @return $this
     */
    public function setNext($node)
    {
        $this->next_node = $node;
        return $this;
    }

    /**
     * @return NodeInterface
     */
    public function getNext()
    {
        if($this->hasNext())
            return $this->next_node;
        return false;
    }

    /**
     * @return bool
     */
    public function hasNext()
    {
        return (null !== $this->next_node);
    }

    /**
     * @param NodeInterface|null $node
     * @return $this
     */
    public function setBracketsNode(NodeInterface $node = null)
    {
        $this->brackets_node = $node;
        return $this;
    }

    /**
     * @return BracketsNode|NodeInterface|null
     */
    public function getBracketsNode()
    {
        return $this->brackets_node;
    }

    /**
     * @param $flag
     */
    public function setFirstFlag($flag){
        $this->first_flag = $flag;
    }

    /**
     * @return bool
     */
    public function getFirstFlag(){
        return $this->first_flag;
    }

    /**
     * @param NodeInterface $node
     * @return NodeInterface
     */
    public function addNext(NodeInterface $node){}

    /**
     * @return string
     */
    public function getLogicString(){
        $logic = $this->getLogic();
        if(TreeConstants::LOGIC_AND == $logic){
            return " AND ";
        }
        return " OR ";
    }

    /**
     * @param $expression
     * @return array
     */
    public function expressionToArray($expression){
        $_operator = '=';
        $field_alias = $_value = "";
        if(2 == count($expression)){
            list($field_alias,$_value) = $expression;
        }elseif(3 == count($expression)){
            list($field_alias,$_operator,$_value) = $expression;
        }
        list($_alias,$_field) = explode(".",$field_alias);
        $_operator = $this->getOperatorName($_operator);
        return compact($_alias,$_field,$_operator,$_value);
    }

    /**
     * @param $operator
     * @return ExpressionNode
     */
    public function setOperator($operator){}

}