<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2020-01-24
 * Time: 11:46
 */

namespace App\Library\ArrayModel\LogicTree;

/**
 * Class AndBrackets
 * @package App\Library\ArrayModel\LogicTree
 */
class AndBrackets extends BracketsNode implements NodeInterface
{
    use AndTrait;

    public $Logic_Operator = TreeConstants::LOGIC_AND;

    /**
     * @param NodeInterface $node
     * @return $this
     */
    public function setPrev(NodeInterface $node)
    {
        $this->parent = $node;
        return $this;
    }

    /**
     * @return NodeInterface
     */
    public function getPrev()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function hasPrev()
    {
        return false;
    }

    /**
     * @return AndBrackets|null
     */
    public function getBracketsNode()
    {
        return $this;
    }

    /**
     * @return bool
     */
    public function couldInsert(){
        return true;
    }

    /**
     * @return Object|array
     */
    public function getDescendants(){
        $descendants = [];
        if ($this->hasNext()){
            $descendants = $this->getNext()->getDescendants();
        }
        return $descendants;
    }

    /**
     * @return array|Object
     */
    public function getSibling(){
        return $this->getDescendants();
    }



}