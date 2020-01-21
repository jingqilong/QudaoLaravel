<?php


namespace App\Library\ArrayModel;


use Closure;
/**
 * Class QueryTrait
 * @package App\Library\ArrayModel
 */
trait QueryTrait
{

    /**
     * @return Wheres
     */
    public function _getWhere(){
        if(null === $this->_wheres){
            $this->_wheres = Wheres::of();
            $this->_wheres->_node_type = Wheres::NODE_TYPE_AGGREGATE;
        }
        return $this->_wheres;
    }

    /**
     * @return Ons
     */
    public function _getOn(){
        if(null === $this->_ons){
            $this->_ons = Ons::of();
        }
        return $this->_ons;
    }

    /**
     * @param $alias
     * @return array
     */
    public function getOrderByFields($alias){
        if($this->_order_bys instanceof OrderBys){
            return $this->_order_bys->getOrderByFields($alias);
        }
        return [];
    }

    /**
     * @param null $keys
     * @param int $level
     * @return array|null
     */
    public function getForeignKeys(&$keys=null,$level=0){
        if($this->_ons instanceof Ons){
            return $this->_ons->getForeignKeys($keys,$level);
        }
        return $keys;
    }

    /**
     * @param null $keys
     * @param int $level
     * @return array|null
     */
    public function getLocalKeys(&$keys=null,$level=0){
        if($this->_ons instanceof Ons){
            return $this->_ons->getLocalKeys($keys,$level);
        }
        return $keys;
    }

    /**
     * @return bool
     */
    private function _initJoin(){
        $join = $this->_join;
        $fields = $this->_fields[$join->_alias];
        $join->_fields[$join->_alias] = $fields;
        return true;
    }

    /**
     * @param $order_bys
     * @return bool
     */
    public function keySort($order_bys){
        $order_by = 'asc';
        if(isset($order_bys[$this->_key])){
            $order_by = $order_bys[$this->_key];
        }
        if('desc' == $order_by){
            krsort($this->data);
        }else{
            ksort($this->data);
        }
        foreach($this->data as $item){
            if($item instanceof QueryList){
                $item->keySort($order_bys);
            }
        }
        return true;
    }

    /**
     * @param $item
     * @param $path
     * @param int $level
     * @return bool
     */
    public function addItem($item,$path,$level=0){
        $key = $item[$path[$level]] ?? '';
        if('' == $key){
            $this->data[] = $item;
            return true;
        }
        $this->_key = $key;
        if (!isset($this->data[$key])) {
            $this->data[$key] = QueryList::of();
        }
        $child = $this->data[$key];
        return $child->addItem($item,$path,$level+1);
    }

    /**
     * @desc load the data for sort and join
     * @return bool
     */
    private function loadData(){
        $path = array_merge($this->_group_bys[$this->_alias], $this->getOrderByFields($this->_alias));
        foreach($this->_from[$this->_alias] as $item){
            $this->addItem($item,$path);
        }
        $order_bys = $this->_order_bys[$this->_alias];
        $this->keySort($order_bys);
        return true;
    }

    /**
     * @desc load the data for sort and join
     * @return bool
     */
    private function loadJoinData(){
        $path = array_merge($this->getLocalKeys(), $this->getOrderByFields($this->_alias));
        foreach($this->_from[$this->_alias] as $item){
            $this->addItem($item,$path);
        }
        $order_bys = $this->_order_bys[$this->_alias];
        $this->keySort($order_bys);
        return true;
    }

    /**
     * @param $closure
     * @return bool
     */
    private function makeJoin(Closure $closure = null){
        return true;
    }

    /**
     * @param Closure|null $closure
     * @return $this
     */
    private function _build(Closure $closure = null){
        if($this->_join instanceof QueryList){
            $this->_initJoin();
            $this->_join->loadJoinData();
        }
        $this->loadData();
        if($this->_join instanceof QueryList){
            $this->makeJoin($closure);
        }
        return $this;
    }

    /**
     * @param $result
     * @param $fields
     * @return bool
     */
    public function readFields(&$result,$fields){
        foreach($this->data as $item){
            if($item instanceof QueryList){
                return $item->readFields($result,$fields);
            }else{
                $new_item = [];
                foreach($fields as $field){
                    $new_item = $item[$field]??'';
                }
                $result[]= $new_item;
            }
        }
        return true;
    }

    /**
     * @return array|null
     */
    public function getOnContains(){
        if($this->_ons instanceof Ons){
            return $this->_ons->getOnContains();
        }
        return [];
    }

    /**
     * 一箇鍵值有一第記錄
     * @param $src_array
     * @param $key
     * @return array
     */
    public static function oneOfKey($src_array,$key){
        $result_array = [];
        foreach($src_array as $item){
            $result_array[$item[$key]] = $item;
        }
        return $result_array;
    }

    /**
     * 每条记录使用多个键值进行排序。
     * @param $src_array
     * @param $keys
     * @param $level
     * @return array
     */
    public static function oneOfKeys($src_array,$keys,$level = 0){
        $result_array = [];
        foreach($src_array as $item){
            if($level < count($keys) -1 ){
                $result_array[$item[$keys[$level]]] = QueryList::oneOfKeys($item,$keys,$level+1);
            }else{
                $result_array[$item[$keys[$level]]] = $item;
            }
        }
        return $result_array;
    }

    /**
     * 通過一箇鍵查到多條記錄幷存在此鍵值爲KEY數組中
     * @param $src_array
     * @param $key
     * @return array
     */
    public static function manyOfKey($src_array,$key){
        $result_array = [];
        foreach($src_array as $item){
            $new_item = & $result_array[$item[$key]];
            $new_item[] = $item;
        }
        return $result_array;
    }


}