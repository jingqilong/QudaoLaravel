<?php
/**
 * Created by PhpStorm.
 * User: Bardeen
 * Date: 2020-01-30
 * Time: 22:55
 */

namespace App\Library\ArrayModel\Query;

use App\Library\ArrayModel\LogicTree\ExpressionNode;
use App\Library\ArrayModel\LogicTree\NodeInterface;
use Closure;

/**
 * Class OnItem
 * @package App\Library\ArrayModel\Query
 * @author Bardeen
 */
class OnItem extends ExpressionNode
{
    /**
     * _alias_join
     *
     * @var string
     */
    protected $_alias_join  = '';

    /**
     * _field_join
     *
     * @var string
     */
    protected $_field_join  = '';

    /**
     * @var bool
     */
    protected $_is_contains = false;

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function set($name,$value){
        if('_operator' == $name){
            if('contains' == $value){
                return $this->setContains($value);
            }
        }
        $this->$name = $value;
        return $this;
    }

    /**
     * @param $value
     * @return bool
     */
    protected function setContains($value){
        $this->_is_contains = $value;
        $this->getBracketsNode()->setContains($value);
        return $this;
    }

     /**
     * @return Ons|NodeInterface|null
     */
    public function getBracketsNode()
    {
        return $this->brackets_node;
    }

    /**
     * Could pass any number of on-conditions and Closures
     *
     * @param array|Closure ...$ons
     * @return $this
     */
    public function on(...$ons){
        return $this->getBracketsNode()->on(...$ons);
    }

    /**
     * Add a on-condition with logic operator or
     *
     * @param $on
     * @return $this
     */
    public function orOn($on){
        return $this->getBracketsNode()->orOn($on);
    }

    /**
     * get the foreign keys from the on-conditions.
     *
     * @param array $keys
     * @return array|null
     */
    public function getForeignKeys(&$keys=[]){
        $keys[$this->_field] = $this->_operator;
        return $keys;
    }

    /**
     * @return string
     */
    public function getMapKey(){
        return $this->_field;
    }

    /**
     * @return JoinMap
     */
    public function toMap(){
        $map = new JoinMap();
        $map->logic = $this->node_logic;
        $map->left_field = $this->_field;
        $map->operator = $this->_operator;
        $map->right_field = $this->_field_join;
        return $map;
    }

}