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
 * Class LinkList
 * @package App\Library\ArrayModel\LogicTree
 */
class LinkList implements Countable
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
     * LinkList constructor.
     */
    public function __construct()
    {
        $this->first_node = NULL;
        $this->last_node = NULL;
        $this->count = 0;
    }

    /**
     * @return mixed
     */
    public function getLogic(){
        return $this->logic;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return ($this->first_node == NULL);
    }

    /**
     * @param NodeInterface $node
     * @return $this
     */
    public function insertFirst($node)
    {
        $node->setFirstFlag(true);
        $this->first_node = $node;
        $this->last_node = $node;
        $this->count++;
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
        $this->last_node = & $node;
        $this->count++;
        return $this;
    }

    /**
     * @param int $node_pos
     * @return null
     */
    public function getNode($node_pos){
        if($node_pos > $this->count){
            return NULL;
        }
        for($pos = 1,$current = $this->getFirst();
            $pos <= $node_pos;
            $pos++) {
            $current = $current->getNext();
            if(null ===  $current){
                return null;
            }
        }
        return $current;
    }

    /**
     * @param NodeInterface $node
     * @return LinkList|void
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
        if(1 == $this->count){
            $this->first_node = $this->last_node = null;
            return $this;
        }
        $this->count--;
        $this->first_node = $this->getFirst()->getNext();
        $this->first_node->setFirstFlag(true);

        return $this;
    }

    /**
     * @return $this
     */
    public function removeLast(){
        if(0 == $this->count()){
            return $this;
        }
        if(1 == $this->count){
            $this->first_node = $this->last_node = null;
            return $this;
        }
        $this->count--;
        $this->last_node = $this->getLast()->getPrev();
        return $this;
    }


    /**
     * @param NodeInterface $node
     * @return $this|LinkList
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
     * @param NodeInterface $newNode
     * @return $this
     */
    public function replaceNode($node,$newNode)
    {
        $prev = $node->getPrev();
        $newNode->setPrev($prev);
        if(null === $prev){
            $this->first_node = $newNode;
        }
        $next = $node->getNext();
        $newNode->setNext($next);
        if(null === $next){
            $this->last_node = $newNode;
        }
        return $this;
    }

    /**
     * @param NodeInterface $node
     * @param NodeInterface $newNode
     * @return $this
     */
    public function insertBefore($node,$newNode){
        if(true == $node->getFirstFlag()){}
        $prev = $node->getPrev();
        $prev->setNext($newNode);
        $newNode->setPrev($prev);
        $newNode->setnext($node);
        $this->count++;
        return $this;
    }

    /**
     * @param NodeInterface $node
     * @param NodeInterface $newNode
     * @return $this
     */
    public function insetAfter($node,$newNode){
        $next = $node->getNext();
        $next->setPrev($newNode);
        $newNode->setPrev($node);
        $newNode->setNext($next);
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
     * @return LinkList|void
     */
    public function push($node){
        return $this->addNode($node);
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
        $node->setNext($this->first_node);
        $this->first_node = $node;
        $this->count++;
        return $this;
    }

    /**
     * @return null|NodeInterface
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