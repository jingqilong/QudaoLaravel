<?php


namespace App\Library\ArrayModel;


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
}