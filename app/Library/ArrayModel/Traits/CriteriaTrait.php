<?php


namespace App\Library\ArrayModel\Traits;

/**
 * Trait CriteriaTrait
 * @package App\Library\ArrayModel
 */
trait CriteriaTrait
{

    /**
     * Operator to function name
     *
     * @var array
     */
    public static $operator_name = [
        "=" => 'eq',
        "!=" => 'neq',
        ">" => 'gt',
        ">=" => 'gte',
        "<" => 'lt',
        "<=" => 'lte',
    ];

    /**
     * function name to operator
     *
     * @var array
     */
    public static $name_operator = [
        "eq"  => "=",
        "neq" => "!=",
        'gt'  => ">" ,
        'gte' => ">=" ,
        'lt'  => "<" ,
        'lte' => "<=",
        'notIn' => 'NOT IN',
    ];



    /**
     * For import sql clause
     * @param $name
     * @return string
     */
    public function getOperator($name){
        $operator = $name;
        if(isset(self::$name_operator[$name])){
            $operator = self::$name_operator[$name];
        }
        return " " . $operator . " ";
    }

    /**
     * For function call
     * @param $operator
     * @return mixed
     */
    public function getOperatorName($operator){
        $name = $operator;
        if(isset(self::$operator_name[$operator])){
            $name = self::$name_operator[$operator];
        }
        return $name;
    }

    /**
     * call the operator function with the function name of operator.
     *
     * @param $value1
     * @param $value2
     * @param $level
     * @return mixed
     */
    protected function callByName($value1,$value2,$level){
        if(null === $value1){
            return $value2;
        }
        $func = $this->_operator;
        $result =  $this->$func($value1,$value2);
        $logic_func = $this->_logic;
        if(("" == $logic_func) || (0 < $level))
            return $result;
        return $this->$logic_func($result);
    }

    /**
     * Operator =
     *
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function eq( $value , $criteria_value){
        return  ($value === $criteria_value);
    }

    /**
     * Operator !=
     *
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function neq( $value , $criteria_value){
        return  ($value !== $criteria_value);
    }

    /**
     * Operator <
     *
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function lt( $value , $criteria_value){
        return  ($value < $criteria_value);
    }

    /**
     * Operator >
     *
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function gt( $value , $criteria_value){
        return  ($value > $criteria_value);
    }

    /**
     * Operator <=
     *
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function lte( $value , $criteria_value){
        return  ($value <= $criteria_value);
    }

    /**
     * Operator >=
     *
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function gte( $value , $criteria_value){
        return  ($value >= $criteria_value);
    }

    /**
     * Operator is
     *
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function is( $value , $criteria_value){
        $function  = "is_" . $criteria_value;
        if(is_callable($function)){
            return  $function($value);
        }
        return false;
    }

    /**
     * Operator not
     *
     * @param $value
     * @return bool
     */
    public function not($value){
        return !(bool)$value;
    }

    /**
     * Operator in
     *
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function in( $value , $criteria_value){
        return  isset($criteria_value[$value]);
    }

    /**
     * Operator not in
     *
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function notIn( $value , $criteria_value){
        return  !isset($criteria_value[$value]);
    }

    /**
     * Operator or
     *
     * @param $value1
     * @param $value2
     * @return bool
     */
    public function or($value1 , $value2){
        return ($value1 || $value2);
    }

    /**
     * Operator and
     *
     * @param $value1
     * @param $value2
     * @return bool
     */
    public function and($value1, $value2){
        return ($value1 && $value2);
    }

    /**
     * Operator contains
     *
     * @param $value1
     * @param $value2
     * @return bool
     */
    public function contains($value1,$value2){
        if(is_string($value1)){
            $keys = explode(',',$value1);
        }else{
            $keys = $value1;
        }
        return in_array($value2,$keys);
    }

    /**
     * Operator like
     *
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function like($value , $criteria_value){
        $needle = trim($criteria_value,"%");
        $match_type = strpos($criteria_value,'%');
        if(0 < $match_type){
            return 0 == strpos($value,$needle);
        }
        $match_type = strlen($criteria_value) - strlen($needle);
        if(2 == $match_type){
            return strpos($value,$needle)>=0;
        }
        return strlen($value) - strlen($needle) == strrpos($value,$needle,-1);
    }
}