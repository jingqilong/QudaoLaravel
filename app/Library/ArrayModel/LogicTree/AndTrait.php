<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2020-01-24
 * Time: 16:32
 */

namespace App\Library\ArrayModel\LogicTree;


trait AndTrait
{

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
     * @param NodeInterface|null $node
     * @return $this
     */
    public function setBracketsNode(NodeInterface $node = null)
    {
        $this->brackets_node = $node;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasNext()
    {
        return (null !== $this->next_node);
    }

    /**
     * @param NodeInterface $node
     * @return $this
     */
    public function addNext(NodeInterface $node)
    {
        if($node instanceof OrNode){
            return $this->addOrNode($node);
        }
        if($this->hasNext()){
            return $this->getNext()->addNext($node);
        }
        $node->setPrev($this);
        $node->setBracketsNode($this->getBracketsNode());
        $this->next_node = $node;
        return $this;
    }

    /**
     * @param $node
     * @return AndBrackets
     */
    public function addOrNode($node){
        $new_node  = new OrBrackets();
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
        $new_node = $node->getNext();
        return $this->addNext($new_node);
    }

    /**
     * @param OrBrackets $node
     * @return $this|NodeInterface
     */
    public function addOrBrackets(OrBrackets $node){
        if($this->hasNext()){
            return $this->getNext()->addNext($node);
        }
        $node->setPrev($this);
        $node->setBracketsNode($this);
        $this->next_node = $node;
        return $this;
    }

    /**
     * @param $node
     */
    public function insertNode($node){
        if(!$this->couldInsert()){
            $this->getPrev()->insertNode($node);
        }

        $old_next = $this->getNext();
        $old_next->setPrev($node);

        $node->setNext($old_next);
        $node->setPrev($this);
        $node->setBracketsNode($this->getBracketsNode());

        $this->setNext($node);

    }
}