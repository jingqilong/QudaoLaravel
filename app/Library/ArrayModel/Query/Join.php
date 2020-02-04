<?php

namespace App\Library\ArrayModel\Query;
use App\Library\ArrayModel\LogicTree\TreeConstants;

/**
 * Class Join
 * @package App\Library\ArrayModel\Query
 * @author Bardeen
 */
class Join extends QueryBuilder
{
    /**
     * Join type: inner join
     */
    const INNER_JOIN = 0;

    /**
     * Join type: left join
     */
    const LEFT_JOIN  = 1;

    /**
     * @var QueryBuilder
     */
    protected $left_list;

    /**
     * @var bool
     */
    protected $is_init = false;

    /**
     * @var array
     */
    protected $_empty_item = [];

    /**
     * Join constructor.
     * @param QueryBuilder $left_list
     */
    public function __construct($left_list = null)
    {
        if( null != $left_list)
            $this->left_list = $left_list;
        parent::__construct();
    }

    /**
     * @param null $empty_item
     * @param $join_type
     * @return static
     * @static
     */
    public static function of($empty_item,$join_type){
        $instance =  new static();
        $instance->_join_type = $join_type;
        $instance->_empty_item = $empty_item;
        return $instance;
    }

    /**
     * @param QueryBuilder $left_list
     * @param $src_array
     * @param $alias
     * @param int $join_type
     * @return static
     * @static
     */
    public static function newJoin($left_list,$src_array,$alias,$join_type = self::INNER_JOIN){
        $instance = new static($left_list);
        $instance->from($src_array,$alias);
        $instance->_join_type = $join_type ;
        return $instance;
    }

    /**
     * @return $this
     */
    public function _init(){
        $this->_fields = $this->left_list->_fields;
        $this->_ons = $this->left_list->_ons;
        $this->_order_bys =  $this->left_list->_order_bys;
        $this->_wheres =  $this->left_list->_wheres;
        return $this;
    }

    /**
     * @desc load the data for sort and join
     * @return bool
     */
    protected function loadData(){
        if(false === $this->is_init){
            $this->_init();
        }
        $path = $this->getOnsKeys();
        foreach($this->_from[$this->_alias] as $item){
            $this->addItemByKey($item,$path);
        }
        return $this;
    }

    /**
     * @return array|null
     */
    public function getOnsKeys(){
        if($this->_ons instanceof Ons){
            return $this->_ons->getOnsKeys();
        }
        return [];
    }


    /**
     * For debugging, import sql clause.
     *
     * @return string
     */
    public function _toSql(){
        $join_set = [' INNER JOIN ',' LEFT JOIN '];
        $result = $join_set[$this->_join_type] . 'sub_table ' . $this->_alias;
        return $result;
    }

    /**
     * @param $name
     * @param $logic
     * @param $item
     * @return string
     */
    public function makeKey($name,$logic,$item){
        $key = ($logic == TreeConstants::LOGIC_OR)? $name .":" : '';
        $key .= $item[$name];
        return $key;
    }

    /**
     * @param $index_keys
     * @param $item
     * @return $this
     */
    public function addItemByKey(&$index_keys,$item){
        foreach($index_keys as $index_key){
            $key = $this->makeKey($index_key['field'],$index_key['logic'],$item);
            if(empty($index_key['children'])){
                $this->data[$key] = $item;
                continue;
            }
            if(empty($this->_empty_item)){
                $this->createEmpty(array_keys($item));
            }
            $child = Join::of($this->_empty_item,$this->_join_type);
            $this->data[$key] = $child ;
            $child->addItemByKey($index_key['children'],$item);
        }
        return $this;
    }

    /**
     * @param $index_keys
     * @param $left_item
     * @param $callback
     * @param int $level
     * @return int
     */
    public function joinItemByKey($index_keys,$left_item,$callback,$level=0){
        $item_count = 0;
        foreach($index_keys as $index_key){
            $key = $this->makeKey($index_key['field'],$index_key['logic'],$left_item);
            if(empty($index_key['children'])){
                call_user_func_array($callback,[$left_item,$this->data[$key]]);
                $item_count++;
                continue;
            }
            $child = $this->data[$key];
            if($child instanceof Join ){
                $item_count += $child->joinItemByKey($index_key['children'],$left_item,$callback,$level+1);
            }
        }
        if((0==$item_count+$level) && (Join::LEFT_JOIN == $this->_join_type)){
            call_user_func_array($callback,[$left_item,$this->_empty_item]);
        }
        return $item_count;
    }
}