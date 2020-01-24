<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2020-01-23
 * Time: 21:02
 */

namespace App\Library\ArrayModel\LogicTree;

/**
 * Class AndNode
 * @package App\Library\ArrayModel\LogicTree
 */
class AndNode extends ExpressionNode implements NodeInterface
{
    use AndTrait;

    public $Logic_Operator = TreeConstants::LOGIC_AND;

    /**
     * @param NodeInterface $node
     * @return $this
     */
    public function setPrev(NodeInterface $node){
        $this->prev_node = $node;
        return $this;
    }

    /**
     * @return NodeInterface
     */
    public function getPrev()
    {
        if($this->hasPrev())
            return $this->prev_node;
        return false;
    }

    /**
     * @return bool
     */
    public function hasPrev()
    {
        return  !(null === $this->prev_node);
    }

    /**
     * @return NodeInterface|null
     */
    public function getBracketsNode(){
        return $this->brackets_node;
    }

    /**
     * @return bool
     */
    public function couldInsert(){
        // if is not in the and chain.
        if(!$this->getBracketsNode() instanceof AndBrackets){
            return false;
        }
        return true;
    }

    /**
     * @return AndBrackets|BracketsNode|OrBrackets|null
     */
    public function getParent(){
        return $this->brackets_node;
    }

    /**
     * @return Object|array
     */
    public function getDescendants(){
        $descendants = [];
        if ($this->hasNext()){
            $descendants = $this->getNext()->getDescendants();
        }
        $descendants[] = $this;
        return $descendants;
    }

    /**
     * @return array|Object
     */
    public function getSibling(){
        return $this->getBracketsNode()->getDescendants();
    }

}