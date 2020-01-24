<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2020-01-24
 * Time: 17:01
 */

namespace App\Library\ArrayModel\LogicTree;


trait OrTrait
{
    /**
     * @return null
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
        if(null !== $node){
            $this->brackets_node = $node;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function couldInsert(){
        return false;
    }

    /**
     * @param $node
     * @return AndBrackets
     */
    public function addAndNode($node){
        $new_node  = new AndBrackets();
        $new_node->addNext($node);
        return $this->addNext($new_node);
    }

    /**
     * @param BracketsNode $node
     * @return $this|AndBrackets|NodeInterface
     */
    public function addBrackets(BracketsNode $node){
        if($node instanceof OrBrackets){
            return $this->addOrBrackets($node);
        }
        if($node instanceof AndBrackets){
            return $this->addAndBrackets($node);
        }
        if($this->HasPrev()){
            return $this->getBracketsNode()->addBrackets($node);
        }
        $brackets = $this->getBracketsNode();
        return $brackets->AddTmpBrackets($node);
    }

    /**
     * @param $node
     * @return AndBrackets
     */
    public function addAndBrackets($node){
        return $this->addNext($node);
    }

    /**
     * @param OrBrackets $node
     * @return $this|NodeInterface
     */
    public function addOrBrackets(OrBrackets $node){
        foreach($node as $item){
            $this->addNext($item);
        }
        return $this;
    }
}