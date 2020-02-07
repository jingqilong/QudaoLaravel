<?php

namespace App\Library\ArrayModel\Query;

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
     * Data : Keep the data of Array
     *
     * @var $data JoinMap
     * @access protected
     */
    protected $data = [];

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
        $this->getJoinMap();
        foreach($this->_from[$this->_alias] as $item){
            $this->data->addItem($item);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function getJoinMap(){
        if($this->_ons instanceof Ons){
            $this->data = $this->_ons->toMap();
        }
        return true;
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
     * @param $left_item
     * @param $callback
     * @return bool
     */
    public function joinItem($left_item,$callback){
        return $this->data->joinItem($left_item,$callback);
    }

    /**
     * @return array
     */
    public function getEmptyItem(){
        if(empty($this->_empty_item)){
            $keys = array_keys($this->_from[0]);
            $this->_empty_item = $this->createEmpty($keys);
        }
        return $this->_empty_item;
    }

}