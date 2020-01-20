<?php


namespace App\Library\ArrayModel;

/**
 * Class QueryTrait
 * @package App\Library\ArrayModel
 */
trait QueryTrait
{

    /**
     * @return Criteria
     */
    public function _getWhere(){
        if(null === $this->_wheres){
            $this->_wheres = Wheres::of();
            $this->_wheres->_node_type = Wheres::NODE_TYPE_AGGREGATE;
        }
        return $this->_wheres;
    }

    /**
     * @return Criteria
     */
    public function _getOn(){
        if(null === $this->_ons){
            $this->_ons = Ons::of();
        }
        return $this->_ons;
    }

    /**
     * @param Closure|null $closure
     */
    private function _init_join(Closure $closure = null){
        $join = $this->_join;
        $fields = $this->_fields[$join->_alias];
        $join->select(...$fields);
        $join->_order_bys = $this->_order_bys->getOrderBys($join->_alias);
        $join->_group_bys = $this->_group_bys->getGroupBys($join->_alias);
    }

    private function loadData(){

    }

    private function makeJoin(){
        
    }

    /**
     * @param Closure|null $closure
     * @return $this
     */
    private function _build(Closure $closure = null){
        if($this->_join instanceof QueryList){
            $this->_init_join($closure);
        }
        $this->loadData();
        if($this->_join instanceof QueryList){
            $this->makeJoin();
        }
        return $this;
    }

    /**
     * @param $result
     * @param $fields
     * @return mixed
     */
    public function readFields(&$result,$fields){
        foreach($this->data as $item){
            if($item instanceof QueryList){
                return $item->readFields($result,$fields);
            }else{
                foreach($fields as $field){
                    $new_item = $item[$field]??'';
                }
                $result[]= $new_item;
            }
        }
    }


    /**
     * 一箇鍵值有一第記錄
     * @param $src_array
     * @param $key
     * @return SortedList
     */
    public static function oneOfKey($src_array,$key){
        $result_array = [];
        foreach($src_array as $item){
            $result_array[$item[$key]] = $item;
        }
        $instance = new static($result_array);
        return $instance;
    }

    /**
     * 每條記錄均使用多箇鍵值排序。
     * @param $src_array
     * @param $keys
     * @param $level
     * @return SortedList
     */
    public static function oneOfKeys($src_array,$keys,$level = 0){
        $result_array = [];
        foreach($src_array as $item){
            if($level < count($keys)-1 ){
                $result_array[$item[$keys[$level]]] = SortedList::oneOfKeys($item,$keys,$level+1);
            }else{
                $result_array[$item[$keys[$level]]] = $item;
            }
        }
        $instance = new static($result_array);
        $instance->_order_bys = $keys[$level];
        return $instance;
    }

    /**
     * 通過一箇鍵查到多條記錄幷存在此鍵值爲KEY數組中
     * @param $src_array
     * @param $key
     * @return SortedList
     */
    public static function manyOfKey($src_array,$key){
        $result_array = [];
        foreach($src_array as $item){
            $new_item = & $result_array[$item[$key]];
            $new_item[] = $item;
        }
        $instance = new static($result_array);
        return $instance;
    }

    /**
     * 傳入 "key1.key2.key3" 進行搜索
     * @param $keyString
     * @return array
     */
    public function getByKeyString($keyString){
        $keys= explode('.',$keyString);
        return $this->findByKeys($keys);
    }
}