<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2020-01-22
 * Time: 18:22
 */

namespace App\Library\ArrayModel\LogicTree;

/**
 * Class Node
 * @package App\Library\ArrayModel\LogicTree
 */
Class Node
{
    /**
     * parent: Reference to the parent node.
     *
     * @var null
     */
    public $parent = null;

    /**
     * logic :Logic operator
     *
     * @var string 'and' , 'or'
     */
    public $logic = '';

    /**
     * result_data : keep the array of data after the logic calculate.
     *
     *
     * @var array
     */
    public $result_data = [];

    /**
     * brackets_node: Keep the brackets node of this node
     *
     * @var BracketsNode|AndBrackets|OrBrackets|null
     */
    public $brackets_node = null;

    /**
     * prev_node: Keep the previous node of this node
     *
     * @var NodeInterface|null
     */
    public $prev_node = null;

    /**
     * next_node: Keep the next node of this node
     *
     * @var NodeInterface|null
     */
    public $next_node = null;

    /**
     * Node constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param null $logic
     * @return AndNode|OrNode
     */
    public function newNode($logic = null){
        if(null === $logic){
            $logic = TreeConstants::LOGIC_AND;
        }
        if( TreeConstants::LOGIC_AND == $logic){
            return new AndNode();
        }
        return new OrNode();
    }

    /**
     * @param $logic
     * @return AndBrackets|BracketsNode|OrBrackets
     */
    public function newBrackets($logic){
        if(null === $logic){
            return new BracketsNode();
        }
        if( TreeConstants::LOGIC_AND == $logic){
            return new AndBrackets();
        }
        return new OrBrackets();
    }

    /**
     * @return Node
     */
    public function getParent(){
        return $this->parent;
    }

    /**
     * @return $this|Node
     */
    public function getAncestor(){
        return $this->getParent()->getAncestor();
    }

    /**
     * @param $node
     */
    public function setPrev($node){
        $this->prev_node = $node;
    }

    /**
     * @return NodeInterface|null
     */
    public function getPrev(){
        return $this->prev_node;
    }

}