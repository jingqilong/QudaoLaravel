<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2020-01-24
 * Time: 11:21
 */

namespace App\Library\ArrayModel\LogicTree;


class OrNode extends ExpressionNode implements NodeInterface
{

    use OrTrait;

    public $Logic_Operator = TreeConstants::LOGIC_OR;

     /**
     * @return null
     */
    public function getPrev()
    {
        if($this->hasPrev())
            return $this->prev_node;
        return false;
    }

    /**
     * @param NodeInterface $node
     * @return $this
     */
    public function setPrev(NodeInterface $node){
        $this->prev_node = $node;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasPrev()
    {
        return (null !== $this->prev_node);
    }

    /**
     * @return AndBrackets|null
     */
    public function getBracketsNode()
    {
        return $this->getParent();
    }

    /**
     * @param NodeInterface $node
     * @return $this
     */
    public function addNext(NodeInterface $node){
        if($node instanceof AndNode){
            return $this->addAndBrackets()->AddNext($node);
        }
        $brackets_node = $this->getBracketsNode();
        $node->setPrev($this->getPrev());
        $node->setBracketsNode($brackets_node);
        $brackets_node[] = $node;
        return $this;
    }

    /**
     * @return Object|array
     */
    public function getDescendants(){
        return $this->getParent()->getChildren();
    }

    /**
     * @return array|Object
     */
    public function getSibling(){
        return $this->getDescendants();
    }

}