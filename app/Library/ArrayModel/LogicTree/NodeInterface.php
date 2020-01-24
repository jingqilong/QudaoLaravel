<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2020-01-24
 * Time: 12:13
 */

namespace App\Library\ArrayModel\LogicTree;

/**
 * Interface NodeInterface
 * @package App\Library\ArrayModel\LogicTree
 */
interface NodeInterface
{
    /**
     * @return NodeInterface
     */
    public function getPrev();

    /**
     * @return NodeInterface
     */
    public function getNext();

    /**
     * @param NodeInterface $node
     * @return NodeInterface
     */
    public function addNext(NodeInterface $node);

    /**
     * @param NodeInterface $node
     * @return NodeInterface
     */
    public function setPrev(NodeInterface $node);

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
     * @return bool
     */
    public function hasNext();

    /**
     * @return bool
     */
    public function hasPrev();

    /**
     * @return mixed
     */
    public function couldInsert();
}