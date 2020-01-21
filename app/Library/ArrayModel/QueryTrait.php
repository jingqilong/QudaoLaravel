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
     * @return mixed
     */
    public function getOrderBys($alias){
        if($this->_order_bys instanceof OrderBys){
            return $this->_order_bys[$alias];
        }
        return [];
    }

    /**
     * @param $alias
     * @return array|mixed
     */
    public function getGroupBys($alias){
        if($this->_group_bys instanceof GroupBys){
            return $this->_group_bys[$alias];
        }
        return [];
    }

    /**
     *
     * @param Closure|null $closure
     * @return bool
     */
    private function _initJoin(Closure $closure = null){
        $join = $this->_join;
        $fields = $this->_fields[$join->_alias];
        $join->_fields[$join->_alias] = $fields;
        $join->_order_bys = $this->getOrderBys($join->_alias);
        $join->_group_bys = $this->getGroupBys($join->_alias);
        if(null !== $closure){
            $join->_from[$join->_alias] = $closure($join);
        }
        return true;
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
            $this->_initJoin($closure);
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