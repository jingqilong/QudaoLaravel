<?php
/**
 * Created by PhpStorm.
 * User: Bardeen
 * Date: 2020-01-26
 * Time: 2:21
 */

namespace App\Library\ArrayModel\LogicTree;

/**
 * Interface NodeInterface
 * @package App\Library\ArrayModel\LogicTree
 */
interface NodeInterface
{

    /**
     * @param NodeInterface $node
     * @return NodeInterface
     */
    public function addNext(NodeInterface $node);

    /**
     * @param NodeInterface|null $node
     * @return NodeInterface
     */
    public function setBracketsNode(NodeInterface $node = null);

    /**
     * @return NodeInterface
     */
    public function getBracketsNode();

    /**
     * @return NodeInterface
     */
    public function getPrev();

    /**
     * @param NodeInterface $node
     * @return NodeInterface
     */
    public function setPrev($node);

    /**
     * @return NodeInterface
     */
    public function getNext();

    /**
     * @param NodeInterface $node
     * @return NodeInterface
     */
    public function setNext($node);


    /**
     * @return int
     */
    public function getLogic();


    /**
     * @return int
     */
    public function getNodeType();

    /**
     * @return string
     */
    public function _toSql();

    /**
     * @param $flag
     * @return mixed
     */
    public function setFirstFlag($flag);

    /**
     * @return mixed
     */
    public function getFirstFlag();
    /**
     * @return string
     */
    public function getLogicString();

    /**
     * @param $operator
     * @return ExpressionNode
     */
    public function setOperator($operator);

}