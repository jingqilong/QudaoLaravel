<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2020-01-24
 * Time: 11:46
 */

namespace App\Library\ArrayModel\LogicTree;

use ArrayAccess,Countable,Iterator ;

class OrBrackets extends BracketsNode implements NodeInterface,ArrayAccess,Countable,Iterator
{
    use OrTrait;

    public $Logic_Operator = TreeConstants::LOGIC_OR;

    /**
     * children : Keep the child nodes.
     *
     * @var array|Node
     */
    public $children = [];

    /**
     * Whether or not an data exists by key
     *
     * @param string ,An data key to check for
     * @access public
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function __isset ($key) {
        return isset($this->children[$key]);
    }

    /**
     * Unsets an data by key
     *
     * @param string ,The key to unset
     * @access public
     */
    public function __unset($key) {
        unset($this->children[$key]);
    }

    /**
     * Assigns a value to the specified offset
     *
     * @param string $offset ,The offset to assign the value to
     * @param mixed  $value ,The value to set
     * @access public
     * @abstracting ArrayAccess
     */
    public function offsetSet($offset,$value) {
        if (is_null($offset)) {
            $this->children[] = $value;
        } else {
            $this->children[$offset] = $value;
        }
    }

    /**
     * Whether or not an offset exists
     *
     * @param string $offset ,An offset to check for
     * @access public
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function offsetExists($offset) {
        return isset($this->children[$offset]);
    }

    /**
     * Unsets an offset
     *
     * @param string $offset ,The offset to unset
     * @access public
     * @abstracting ArrayAccess
     */
    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->children[$offset]);
        }
    }

    /**
     * Returns the value at specified offset
     *
     * @param string $offset ,The offset to retrieve
     * @access public
     * @return mixed
     * @abstracting ArrayAccess
     */
    public function offsetGet($offset) {
        return $this->offsetExists($offset) ? $this->data[$offset] : null;
    }

    /**
     * @return int|void
     */
    public function count(){
        return count($this->children);
    }

    /**
     * @return mixed|void
     */
    function rewind() {
        return reset($this->children);
    }

    /**
     * @return mixed
     */
    function current() {
        return current($this->children);
    }

    /**
     * @return int|mixed|string|null
     */
    function key() {
        return key($this->children);
    }

    /**
     * @return mixed|void
     */
    function next() {
        return next($this->children);
    }

    /**
     * @return bool
     */
    function valid() {
        return key($this->children) !== null;
    }

    /**
     * @return null
     */
    public function getPrev()
    {
        return false;
    }
    
    /**
     * @param NodeInterface $node
     * @return $this
     */
    public function setPrev(NodeInterface $node){
        $this->parent = $node;
        return $this;
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
     * @param NodeInterface $node
     * @return $this
     */
    public function addNext(NodeInterface $node){
        if($node instanceof AndNode){
            return $this->addAndBrackets()->AddNext($node);
        }
        $prev_node = end($this->children);
        if(false === $prev_node){
            $prev_node = $this;
        }
        $node->setPrev($prev_node);
        $node->setBracketsNode($this);
        $this->children[] = $node;
        return $this;
    }

    /**
     * @return Node|array
     */
    public function getChildren(){
        return $this->children;
    }

    /**
     * @return Object|array
     */
    public function getDescendants(){
        return $this->children;
    }

    /**
     * @return array|Object
     */
    public function getSibling(){
        return $this->getDescendants();
    }

}