<?php
/**
 * Created by PhpStorm.
 * User: Bardeen
 * Date: 2020-02-06
 * Time: 1:34
 */

namespace App\Library\ArrayModel\Query;


use App\Library\ArrayModel\Abstracts\MultiMap;

/**
 * Class JoinMap
 * @package App\Library\ArrayModel\Query
 */
class JoinMap extends MultiMap
{
    /**
     * @var string
     */
    public $logic = '';

    /**
     * @var string
     */
    public $left_field = '';

    /**
     * @var string
     */
    public $operator = '';

    /**
     * @var string
     */
    public $right_field = '';

    /**
     * @var null|JoinMap
     */
    public $next = null;

    /**
     * @var null
     */
    public $child = null;

    /**
     * @var array
     */
    public $empty_item = [];

    /**
     * @var int
     */
    public $join_type = 0;

    /**
     * @param $left_item
     * @param $key
     * @return array
     */
    protected function explodeIndexes($left_item,$key){
        if('contains' == $this->operator){
            $keys_string = $left_item[$key];
            $keys = explode(',', trim($keys_string,',').'');
        }else{
            $keys = [$left_item[$key]];
        }
        return $keys;
    }

    /**
     * @param $empty_item
     * @param $join_type
     */
    public function setProperties($empty_item,$join_type){
        $this->empty_item = $empty_item;
        $this->join_type = $join_type;
        if($this->next instanceof JoinMap){
            $this->next->setProperties($empty_item,$join_type);
        }
        foreach($this->child as $key => $child){
            /** @var JoinMap $child */
            $child->setProperties($empty_item,$join_type);
        }
    }

    /**
     * @param $item
     * @return bool
     */
    public function addItem($item){
        $key = 'next';
        $index = 0;
        $new_node = $item;
        if($this->next instanceof JoinMap){
            /** @var JoinMap $new_node */
            $new_node = clone $this->next;
            $new_node->addItem($item);
        }
        if(!empty($this->right_field) && isset($item[$this->right_field])){
            $key = $this->left_field;
            $index = $item[$this->right_field];
        }
        $this->data[$key][$index] = $new_node;
        foreach($this->child as $key => $child){
            /** @var JoinMap $new_node */
            $new_node = clone $child;
            $this->data['child'][$key] = $new_node;
            $new_node->addItem($item);
        }
        return true;
    }

    /**
     * @param $left_item
     * @param null $result
     * @return MultiMap|null
     */
    public function getItem($left_item,&$result=null){
        if(null == $result){
            $result = new MultiMap();
        }
        $key = $this->left_field ?? '';
        if(!empty($key)){
            $indexes = $this->explodeIndexes($left_item,$key);
            foreach($indexes as $index){
                if(isset($this->data[$key][$index])){
                    $result[$index] = $this->data[$key][$index];
                }
            }
        }
        $child = $this->data['next'][0];
        /** @var JoinMap $child */
        $child->getItem($left_item,$result);
        foreach($this->data['child'] as $child){
            /** @var JoinMap $child */
            $child->getItem($left_item,$result);
        }
        return $result;
    }

    /**
     * @param $left_item
     * @param $callback
     * @return MultiMap
     */
    public function joinItem($left_item,$callback){
        $key = $this->left_field ?? '';
        if(!empty($key)){
            $indexes = $this->explodeIndexes($left_item,$key);
            foreach($indexes as $index){
                if(isset($this->data[$key][$index])){
                    $right_item = $this->data[$key][$index];
                    call_user_func_array($callback,[$left_item,$right_item]);
                }
            }
        }
        $child = $this->data['next'][0];
        /** @var JoinMap $child */
        $child->joinItem($left_item,$callback);
        foreach($this->data['child'] as $child){
            /** @var JoinMap $child */
            $child->joinItem($left_item,$callback);
        }
        return true;
    }
}