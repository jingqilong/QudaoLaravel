<?php


namespace App\Library\ArrayModel\Components;

use App\Library\ArrayModel\Abstracts\Criteria;
use App\Library\ArrayModel\FieldTrait;

use Closure;
/**
 * Class Wheres
 * @package App\Library\ArrayModel
 */
class Wheres extends Criteria
{

    use FieldTrait;

    /**
     * create a object statically
     *
     * @return Wheres
     */
    public static function of(){
        $instance = new static();
        return $instance;
    }

    /**
     * @desc The format of arguments is
     *      Array:  ['a.table.field','=' 'value'] ,['b.table.field','asc']  ...
     *      Closure: function($query)use($data){
     *              $query->where(['table.field','=' 'value'])
     *      }
     * @param array|Closure ...$wheres
     * @return $this
     */
    public function where(...$wheres){
        $logic = ['','and']; $i=0;
        foreach($wheres as $where){
            if($where instanceof Closure){
                $group = $this->whereBrackets();
                $where->bindTo($group);
                $where($group);
                continue;
            }
            $this->_addWhere($where,null,$logic[$i]);
            $i=1;
        }
        return $this;
    }

    /**
     * @return Wheres
     */
    public function whereBrackets(){
        $new_where = self::of();
        $new_where->_node_type = self::NODE_TYPE_AGGREGATE;
        $this->_children = $new_where;
        return $new_where;
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereIn($where){
        $this->_addWhere($where,'in','and');
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereNotIn($where){
        $this->_addWhere($where,'notIn','and');
        return $this;
    }

    /***
     * @param $where
     * @return $this
     */
    public function orWhereNotIn($where){
        $this->_addWhere($where,'notIn','or');
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereIn($where){
        $this->_addWhere($where,'in','or');
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhere($where){
        $this->_addWhere($where,'','or');
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function whereBetween($where){
        $wheres = [];
        list($field,$min,$max) = $where;
        $wheres[] = [$field,'>=',$min];
        $wheres[] = [$field,'<=',$max];
        $this->_addWheres($wheres,'and','and');
        return $this;
    }

    /**
     * @param $where
     * @return $this
     */
    public function orWhereBetween($where){
        $wheres = [];
        list($field,$min,$max) = $where;
        $wheres[] = [$field,'>=',$min];
        $wheres[] = [$field,'<=',$max];
        $this->_addWheres($wheres,'and','or');
        return $this;
    }

    /**
     * For debugging, import sql
     *
     * @param int $level
     * @return string
     */
    public function toSql($level = 0){
        $result = '';
        if (0 == $level )
            $result .= "WHERE ";
        if(!empty($this->_logic))
            $result .= " " .$this->_logic . " ";
        $result .= parent::_toSql($level);
        return $result;
    }

    /**
     * calculate the value of the where conditions.
     *
     * @param $cur_values
     * @param int $level
     * @return mixed|null
     */
    public function _getValue($cur_values,$level = 0){
        $result = null;
        if(self::NODE_TYPE_AGGREGATE == $this->_node_type){
            foreach ($this->_children as $node) {
                $value = ($node instanceof Wheres)? $node->_getValue($cur_values,$level + 1) : false;
                if (null !== $result) {
                    $func = $node->_logic;
                    $result = $this->$func($result, $value);
                } else {
                    $result = $value;
                }
            }
            return $result;
        }
        $value = $cur_values[$this->_field];
        $result = $this->callByName($value,$this->_criteria_value,$level);
        return $result;
    }

    /**
     * add where condition
     *
     * @param array|Closure $where
     * @param $operator
     * @param $logic
     * @param int $level
     */
    public function _addWhere($where,$operator=null,$logic='',$level=0){
        if(0==$level) {
            $node = Wheres::of();
            $node->_addWhere($where, $operator, $logic, $level + 1);
            $this->_children[] = $node;
        }
        $this->_node_type = self::NODE_TYPE_EQUATION;
        if(is_array($where)){
            if(2==count($where)){
                list($column,$value) = $where;
                if(empty($operator)){
                    $operator = 'eq';
                }
            }else{
                list($column,$operator,$value) = $where;
                $operator = $this->getOperatorName($operator);
            }
            $this->_logic = $logic;
            $field_set = explode('.',$column);
            list($alias,$field) = $field_set;
            $this->_alias = $alias;
            $this->_field = $field;
            $this->_operator = $operator;
            $this->_criteria_value = $value;
        }
    }

    /**
     * add some where conditions.
     *
     * @param $wheres
     * @param $inner_logic
     * @param $group_logic
     * @param int $level
     */
    public function _addWheres($wheres,$inner_logic,$group_logic,$level=0){
        $node = Wheres::of();
        $node->_logic = $group_logic;
        $this->_children[]=$node;
        $logic = ['',$inner_logic]; $i=0;
        foreach($wheres as $where){
            $node->_addWhere($where,null,$logic[$i],$level+1);
        }
    }
}