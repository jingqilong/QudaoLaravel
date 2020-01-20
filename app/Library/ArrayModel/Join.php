<?php


namespace App\Library\ArrayModel;


class Join extends QueryList
{
    const INNER_JOIN = 0;
    const LEFT_JOIN  = 1;
    const RIGHT_JOIN = 2;

    /**
     * @param $src_array
     * @param $alias
     * @param $join_type
     * @return Join
     */
    public static function init($src_array,$alias,$join_type = self::INNER_JOIN){
        $instance = new static();
        $instance->from($src_array,$alias);
        $instance->_join_type = $join_type ;
        return $instance;
    }

    public function _toSql(){
        $join_set = [' INNER JOIN ',' LEFT JOIN ',' RIGHT JOIN '];
        $result = $join_set[$this->_join_type] . 'sub_table' . $this->_alias;
        return $result;
    }
}