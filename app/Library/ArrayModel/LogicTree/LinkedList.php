<?php
/**
 * Created by PhpStorm.
 * User: Bardeen
 * Date: 2020-01-29
 * Time: 20:46
 */

namespace App\Library\ArrayModel\LogicTree;

use Countable;

/**
 * Class LinkedList
 * @package App\Library\ArrayModel\LogicTree
 * @author Bardeen
 */
class LinkedList implements Countable
{
    /**
     * Link to the first node in the list
     * @var null
     */
    private $first_node;

    /**
     * Link to the last node in the list
     * @var null
     */
    private $last_node;

    /**
     * Total nodes in the list
     * @var int
     */
    private $count;

    /**
     * @var int
     */
    private $logic;

    /**
     * LinkedList constructor.
     */
    public function __construct()
    {
        $this->first_node = $this->last_node = null;
        $this->count = 0;
    }

    /**
     * @return mixed
     */
    public function getLogic()
    {
        return $this->logic;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return (null === $this->first_node);
    }

    /**
     * @param NodeInterface $node
     * @return $this
     */
    public function insertFirst($node)
    {
        $node->setFirstFlag(true);
        $this->count++;
        if(1 == $this->count){
            $this->first_node = $this->last_node = $node;
        }
        $this->first_node->setFirstFlag(false);
        $node->setNext($this->first_node);
        $this->first_node = $node;
        return $this;
    }

    /**
     * @param NodeInterface $node
     * @return $this
     */
    public function insertLast($node)
    {
        $prev = $this->getLast();
        $node->setPrev($prev);
        $prev->setNext($node);
        $this->last_node = $node;
        $this->count++;
        return $this;
    }

    /**
     * @param int $node_pos
     * @return null
     */
    public function getNode($node_pos){
        $current = $this->getFirst();
        for($pos=0; $pos<=$node_pos; $pos++) {
            $current = $current->getNext();
        }
        return $current;
    }

    /**
     * @param NodeInterface $node
     * @return LinkedList|void
     */
    public function addNode($node){
        if(0 == $this->count){
            return $this->insertFirst($node);
        }
        if(1 == $this->count) {
            $this->logic = $node->getLogic();
        }
        return $this->insertLast($node);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * @return array
     */
    public function getList()
    {
        $listData = [];
        $current = $this->getFirst();
        while(null !== $current)
        {
            array_push($listData, $current);
            $current = $current->getNext();
        }
        return $listData;
    }

    /**
     * @return array
     */
    public function getReverseList()
    {
        $listData = [];
        $current = $this->getLast();
        while(null !== $current)
        {
            array_push($listData, $current);
            $current = $current->getPrev();
        }
        return $listData;
    }

    /**
     * @return $this
     */
    public function removeFirst(){
        if(0 == $this->count()){
            return $this;
        }
        $this->count--;
        if(1 == $this->count){
            $this->first_node = $this->last_node = null;
            return $this;
        }
        $this->first_node = $this->getFirst()->getNext();
        $this->first_node->setFirstFlag(true);
        $this->first_node->setPrev(null);
        return $this;
    }

    /**
     * @return $this
     */
    public function removeLast(){
        if(0 == $this->count()){
            return $this;
        }
        $this->count--;
        if(1 == $this->count){
            $this->first_node = $this->last_node = null;
            return $this;
        }
        $this->last_node = $this->getLast()->getPrev();
        $this->last_node->setNext(null);
        return $this;
    }


    /**
     * @param NodeInterface $node
     * @return $this|LinkedList
     */
    public function removeNode($node){
        $prev = $node->getPrev();
        if(null == $prev){
            return $this->removeFirst();
        }
        $next = $node->getNext();
        if(null == $next){
            return $this->removeLast();
        }
        $prev->setNext($next);
        $next->setPrev($prev);
        $this->count--;
        return $this;
    }

    /**
     * @param NodeInterface $node
     * @param NodeInterface $new_node
     * @return $this
     */
    public function replaceNode($node,$new_node)
    {
        $prev = $node->getPrev();
        $new_node->setPrev($prev);
        if(null === $prev){
            $this->first_node = $new_node;
            $this->first_node->setFirstFlag(true);
        }
        $next = $node->getNext();
        $new_node->setNext($next);
        if(null === $next){
            $this->last_node = $new_node;
        }
        return $this;
    }

    /**
     * @param NodeInterface $node
     * @param NodeInterface $new_node
     * @return $this
     */
    public function insertBefore($node,$new_node){
        if(true == $node->getFirstFlag()){
            return $this->insertFirst($new_node);
        }
        $prev = $node->getPrev();
        $prev->setNext($new_node);
        $new_node->setPrev($prev);
        $new_node->setnext($node);
        $this->count++;
        return $this;
    }

    /**
     * @param NodeInterface $node
     * @param NodeInterface $new_node
     * @return $this
     */
    public function insetAfter($node,$new_node){
        $next = $node->getNext();
        if(null === $next){
            return $this->insertLast($new_node);
        }
        $next->setPrev($new_node);
        $new_node->setPrev($node);
        $new_node->setNext($next);
        $this->count++;
        return $this;
    }

    /**
     * @return null
     */
    public function pop(){
        $node = $this->last_node;
        $this->removeLast();
        return $node;
    }

    /**
     * @param $node
     * @return LinkedList|void
     */
    public function push($node){
        return $this->insertLast($node);
    }

    /**
     * @return null
     */
    public function shift(){
        $node = $this->first_node;
        $this->removeFirst();
        return $node;
    }

    /**
     * @param NodeInterface $node
     * @return $this
     */
    public function unShift($node){
        return $this->insertFirst($node);
    }

    /**
     * @return null|NodeInterface|
     */
    public function getFirst(){
        return $this->first_node;
    }

    /**
     * @return NodeInterface|null
     */
    public function getLast(){
        return $this->last_node;
    }

    /**
     * @return \Generator
     */
    public function items(){
        $current = $this->getFirst();
        while(null !== $current)
        {
            yield $current;
            $current = $current->getNext();
        }
    }

}