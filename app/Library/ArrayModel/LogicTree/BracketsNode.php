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
    public $node_type = TreeConstants::NODE_TYPE_AGGREGATE;

    /**
     * @var null|string
     */
    public $node_logic = null;

    /**
     * @var null|string
     */
    public $inner_logic = null;

    /**
     * @var null|array
     */
    public $children = [];

    /**
     * @var bool
     */
    public $reduced = false;

    /**
     * BracketsNode constructor.
     */
    public function __construct()
    {
        $this->children[TreeConstants::LOGIC_AND] = new LinkList();
        $this->children[TreeConstants::LOGIC_OR] = new LinkList();
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
     * @param array $expression
     * @param null $logic
     * @param $operator
     * @return ExpressionNode
     */
    public function newNode($expression,$logic,$operator){
        $node = parent::newNode($expression,$logic,$operator);
        return $node;
    }

    /**
     * @param int $logic
     * @return NodeInterface
     */
    public function newBracketsNode($logic)
    {
        return parent::newBracketsNode($logic);
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
    public function _addNode(NodeInterface $node)
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
    public function _addBracketsNode(NodeInterface $node)
    {
        if ($node instanceof ExpressionNode) {
            return $this->_addNode($node);
        }
        $logic = $node->getLogic();
        return $this->getChildren($logic)->addNode($node);
    }

    /**
     * @param NodeInterface|Node $node
     * @return $this|NodeInterface|Node
     */
    public function _addLeftNode(NodeInterface $node)
    {
        $logic = $node->getLogic();
        $children = $this->getChildren($logic);
        $old_node = $children->getFirst();
        if (null === $old_node) {
            return $children->insertFirst($node);
        }
        return $children->insertBefore($old_node, $node);
    }

    /**
     * @param NodeInterface|Node $node
     * @return $this|NodeInterface|Node
     */
    public function _addAndNode(NodeInterface $node)
    {
        return $this->addNext($node);
    }

    /**
     * @param NodeInterface|Node $node
     * @return $this|NodeInterface|Node
     */
    public function _addOrNode(NodeInterface $node)
    {
        return $this->addNext($node);
    }

    /**
     * @param NodeInterface|Node $node
     * @return NodeInterface|Node
     */
    public function _addLeftBracketsNode(NodeInterface $node)
    {
        return $this->addNext($node);
    }

    /**
     * @param NodeInterface|Node $node
     * @return NodeInterface|Node
     */
    public function _addAndBracketsNode(NodeInterface $node)
    {
        return $this->addNext($node);
    }

    /**
     * @param NodeInterface|Node $node
     * @return NodeInterface|Node
     */
    public function _addOrBracketsNode(NodeInterface $node)
    {
        return $this->addNext($node);
    }

    /**
     * @param array |Node $nodes
     * @return NodeInterface|Node
     */
    public function _addAndNodes(...$nodes)
    {
        foreach ($nodes as $key => $node) {
            $this->_addAndNode($node);
        }
        return $this;
    }

    /**
     * @param array $nodes
     * @return NodeInterface|Node
     */
    public function _addOrNodes(...$nodes)
    {
        foreach ($nodes as $key => $node) {
            $this->_addOrNode($node);
        }
        return $this;
    }

    /**
     * @param array $expression
     * @param string $logic
     * @param $operator
     * @return NodeInterface
     */
    public function addNode($expression = [], $logic = '',$operator)
    {
        $node = $this->newNode($expression, $logic,$operator);
        return $this->addNext($node);
    }

    /**
     * @param array $expression
     * @param $operator
     * @return NodeInterface
     */
    public function addAndNode($expression = [],$operator)
    {
        $node = $this->newNode($expression, TreeConstants::LOGIC_AND,$operator);
        return $this->addNext($node);
    }

    /**
     * @param array $expression
     * @param $operator
     * @return NodeInterface
     */
    public function addOrNode($expression = [],$operator)
    {
        $node = $this->newNode($expression, TreeConstants::LOGIC_OR,$operator);
        return $this->addNext($node);
    }

    /**
     * @param array ...$expressions
     * @return $this
     */
    public function addAndNodes(...$expressions)
    {
        $logic = TreeConstants::LOGIC_AND;
        foreach ($expressions as $expression) {
            $node = $this->newNode($expression, $logic,'');
            $this->addNext($node);
        }
        return $this;
    }

    /**
     * @param ...$expressions
     * @return $this
     */
    public function addOrNodes(...$expressions)
    {
        $logic = TreeConstants::LOGIC_OR;
        foreach ($expressions as $expression) {
            $node = $this->newNode($expression, $logic,'');
            $this->addNext($node);
        }
        return $this;
    }

    /**
     * @param $logic
     * @return NodeInterface
     */
    public function addBracketsNode($logic)
    {
        $node = $this->newBracketsNode($logic);
        $this->addNext($node);
        return $node;
    }

    /**
     * @return NodeInterface
     */
    public function addAndBracketsNode()
    {
        $logic = TreeConstants::LOGIC_AND;
        $node = $this->newBracketsNode($logic);
        $this->addNext($node);
        return $node;
    }

    /**
     * @return NodeInterface
     */
    public function addOrBracketsNode()
    {
        $logic = TreeConstants::LOGIC_OR;
        $node = $this->newBracketsNode($logic);
        $this->addNext($node);
        return $node;
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
        foreach ($sub_children as $sub_child) {
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
    public function reduceNodes($logic){
        $children = $this->getChildren($logic);
        foreach ($children as $child) {
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
        foreach ($children as $node) {
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
        foreach ($children as $node) {
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