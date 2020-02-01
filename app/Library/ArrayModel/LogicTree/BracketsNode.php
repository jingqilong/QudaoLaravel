<?php
/**
 * Created by PhpStorm.
 * User: Bardeen
 * Date: 2020-01-26
 * Time: 2:09
 */
namespace App\Library\ArrayModel\LogicTree;

/**
 * Class BracketsNode
 * @package App\Library\ArrayModel\LogicTree
 */
class BracketsNode extends Node implements NodeInterface
{
    use NodeTrait;

    /**
     * @var int
     */
    protected $node_type = TreeConstants::NODE_TYPE_AGGREGATE;

    /**
     * @var null|string
     */
    protected $node_logic = null;

    /**
     * @var null|string
     */
    protected $inner_logic = null;

    /**
     * @var LinkList children[]
     */
    protected $children = [];

    /**
     * @var bool
     */
    protected $reduced = false;

    /**
     * BracketsNode constructor.
     */
    public function __construct()
    {
        $this->children[TreeConstants::LOGIC_AND] = new LinkList();
        $this->children[TreeConstants::LOGIC_OR] = new LinkList();
    }

    /**
     * @param array $expression
     * @param string $logic
     * @param string $operator
     * @return ExpressionNode
     */
    public static function newNode($expression,$logic=TreeConstants::LOGIC_AND, $operator='='){
        return ExpressionNode::newNode($expression,$logic,$operator);
    }

    /**
     * @param $logic
     * @return BracketsNode|NodeInterface
     *
     */
    public static function newBracketsNode($logic = TreeConstants::LOGIC_AND){
        $new_node = new BracketsNode();
        return $new_node->setLogic($logic);
    }

    /**
     * @param $logic
     * @return NodeInterface
     */
    public function firstChild($logic)
    {
        return $this->getChildren($logic)->getFirst();
    }

    /**
     * @param $logic
     * @return NodeInterface|Node
     */
    public function getLast($logic)
    {
        return $this->getChildren($logic)->getLast();
    }

    /**
     * @param $logic
     * @param $node
     * @return $this
     */
    public function setLast($logic, $node)
    {
        return $this->getChildren($logic)->insertLast($node);
    }

    /**
     * @param $logic
     * @return LinkList
     */
    public function getChildren($logic)
    {
        return $this->children[$logic];
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        $count = count($this->children[TreeConstants::LOGIC_AND])
            + count($this->children[TreeConstants::LOGIC_OR]);
        return (0 == $count);
    }

    /**
     * @param NodeInterface|Node $node
     * @return NodeInterface
     */
    public function addNext(NodeInterface $node)
    {
        $logic = $node->getLogic();
        $this->getChildren($logic)->addNode($node);
        return $node;
    }

    /**
     * @param NodeInterface|Node $node
     * @return NodeInterface|Node|bool
     */
    protected function _addNode(NodeInterface $node)
    {
        if ($node instanceof BracketsNode) {
            return $this->_addBracketsNode($node);
        }
        $logic = $node->getLogic();
        return $this->getChildren($logic)->addNode($node);
    }

    /**
     * @param NodeInterface|Node $node
     * @return NodeInterface|Node|bool
     */
    protected function _addBracketsNode(NodeInterface $node)
    {
        if ($node instanceof ExpressionNode) {
            return $this->_addNode($node);
        }
        $logic = $node->getLogic();
        return $this->getChildren($logic)->addNode($node);
    }

    /**
     * @return string
     */
    public function getInnerLogic()
    {
        $or_children = $this->children[TreeConstants::LOGIC_OR];
        if (0 < count($or_children)) {
            return TreeConstants::LOGIC_OR;
        }
        return TreeConstants::LOGIC_AND;
    }

    /**
     * @desc  associative law
     * @param LinkList $children
     * @param BracketsNode|NodeInterface $child
     * @param $logic
     * @return bool
     */
    protected function associativeNodes($children, $child, $logic)
    {
        $sub_children = $child->getChildren($logic);
        foreach ($sub_children->items() as $sub_child) {
            if (TreeConstants::NODE_TYPE_AGGREGATE == $sub_child->getNodeType()) {
                continue;
            }
            $children->insertFirst($sub_child);
            $sub_children->removeNode($sub_child);
        }
        return true;
    }

    /**
     * @desc commutative law
     * @param LinkList $children
     * @param BracketsNode|NodeInterface $child
     * @param bool $is_push
     * @return bool
     */
    protected function commutativeNodes($children, $child, $is_push = true)
    {
        $children->removeNode($child);
        if (true === $is_push) {
            $children->insertLast($child);
        } else {
            $children->insertFirst($child);
        }
        return true;
    }

    /**
     * @param $logic
     * @return bool
     */
    protected function reduceNodes($logic){
        $children = $this->getChildren($logic);
        foreach ($children->items() as $child) {
            if ($child instanceof BracketsNode) {
                if (true === $child->reduced) {
                    continue;
                }
                $child->reduceLogic();
                if ($logic == $child->getInnerLogic()) {//reduce and-or-and to and-and-or
                    $this->associativeNodes($children, $child, $logic);
                    if ($child->isEmpty()) {
                        $children->removeNode($child);
                    }
                } else { //reduce and-(and-and) to and-and-and
                    $is_push = (TreeConstants::LOGIC_OR === $logic);
                    $this->commutativeNodes($children, $child, $is_push);
                }
                $child->reduced = true;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function reduceLogic()
    {
        $this->reduceNodes(TreeConstants::LOGIC_AND);
        $this->reduceNodes(TreeConstants::LOGIC_OR);
        return true;
    }

    /**
     * @param $logic
     * @return string
     */
    protected function _toSqlLogic($logic)
    {
        $children = $this->getChildren($logic);
        if (0 == count($children)) {
            return '';
        }
        $result = "(";
        foreach ($children->items() as $node) {
            $result .= $node->_toSql();
        }
        return $result . ")";
    }

    /**
     * @return string
     */
    public function _toSql()
    {
        $result = "";
        if (false === $this->getFirstFlag()) {
            $result .= $this->getLogicString();
        }
        $result .= "(";
        $result .= $this->_toSqlLogic(TreeConstants::LOGIC_AND);
        $result .= $this->_toSqlLogic(TreeConstants::LOGIC_OR);
        return $result . ")";
    }

    /**
     * @param $cur_values
     * @param $logic
     * @param $level
     * @return null
     */
    protected function getValueLogic($cur_values,$logic,$level){
        $result = $value =  null;
        $children = $this->getChildren($logic);
        /* var $node BracketNode|NodeInterface */
        foreach ($children->items() as $node) {
            $value = $node->_getValue($cur_values,$level + 1);
            if (null !== $result) {
                $func = $node->getLogic();
                $result = $this->$func($result, $value);
            } else {
                $result = $value;
            }
        }
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
        $value_and = $this->getValueLogic($cur_values,TreeConstants::LOGIC_AND,$level);
        $value_or = $this->getValueLogic($cur_values,TreeConstants::LOGIC_OR,$level);
        return $this->or($value_and,$value_or);
    }

}