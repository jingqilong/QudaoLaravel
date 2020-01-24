<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2020-01-24
 * Time: 11:59
 */

namespace App\Library\ArrayModel\LogicTree;


class BracketsNode extends Node
{

    /**
     * @var null
     */
    public $Logic_Operator = null;

    /**
     * @var array
     */
    public $tmp_brackets = [];

    /**
     * @var null
     */
    public $hold_key = null;

    /**
     * @param $node
     * @return mixed
     */
    public function holdBrackets($node){
        $this->tmp_brackets[] = $node;
        return max(array_keys($this->tmp_brackets));
    }

    /**
     * @param $hold_key
     * @return bool
     */
    public function removeBrackets($hold_key){
        unset($this->tmp_brackets[$hold_key]);
        return true;
    }

    /**
     * @return bool
     */
    public function isHolded(){
        return (null !== $this->hold_key);
    }

    /**
     * @param $logic
     * @return mixed
     */
    public function removeHoldBrackets($logic){

        $prev = $this->getPrev();
        if(true === $prev->isHolded()){
            return $prev->removeHoldBrackets($logic);
        }
    }

    /**
     * @param $node
     */
    public function AddTmpBrackets($node){
        $node->setPrev($this);
        $this->hold_key = $this->holdBrackets($node);
        return $node;
    }

    /**
     * @param $node
     * @return AndBrackets|BracketsNode|OrBrackets
     */
    public function addNext($node){
        if(null !== $node->logic_operator){
            $new_node = $this->newBrackets($node->logic_operator);
            $this->removeBrackets($this->hold_key);
            $new_node->addNext($node);
            $prev = $this->getPrev();
            $prev->addBrackets($new_node);
            $prev->$this->removeHoldBrackets($node->logic_operator);
            return $new_node;
        }
    }

    /**
     * @param $node
     * @return AndBrackets
     */
    public function addOrNode($node){

    }

    /**
     * @param $node
     * @return AndBrackets
     */
    public function addAndNode($node){
        if(null !== $node->logic_operator){
            $new_node = $this->newBrackets($node->logic_operator);
            $this->removeBrackets($this->hold_key);
            $new_node->addNext($node);
            $this->getPrev()->addBrackets($new_node);
            return $new_node;
        }
    }

    /**
     * @param BracketsNode $node
     * @return $this|AndBrackets|NodeInterface
     */
    public function addBrackets(BracketsNode $node){

    }

    /**
     * @param $node
     * @return AndBrackets
     */
    public function addAndBrackets($node){

    }

    /**
     * @param OrBrackets $node
     * @return $this|NodeInterface
     */
    public function addOrBrackets(OrBrackets $node){

    }
}