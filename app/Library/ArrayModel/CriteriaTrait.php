<?php


namespace App\Library\ArrayModel;


trait CriteriaTrait
{

    public static $operator_name = [
        "=" => 'eq',
        "!=" => 'neq',
        ">" => 'gt',
        ">=" => 'gte',
        "<" => 'lt',
        "<=" => 'lte',
    ];

    public static $name_operator = [
        "eq"  => "=",
        "neq" => "!=",
        'gt'  => ">" ,
        'gte' => ">=" ,
        'lt'  => "<" ,
        'lte' => "<=",
    ];

    /**
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
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function eq( $value , $criteria_value){
        return  ($value === $criteria_value);
    }

    /**
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function neq( $value , $criteria_value){
        return  ($value !== $criteria_value);
    }

    /**
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function lt( $value , $criteria_value){
        return  ($value < $criteria_value);
    }

    /**
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function gt( $value , $criteria_value){
        return  ($value > $criteria_value);
    }

    /**
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function lte( $value , $criteria_value){
        return  ($value <= $criteria_value);
    }

    /**
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function gte( $value , $criteria_value){
        return  ($value >= $criteria_value);
    }

    /**
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
     * @param $value
     * @return bool
     */
    public function not($value){
        return !(bool)$value;
    }

    /**
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function in( $value , $criteria_value){
        return  isset($criteria_value[$value]);
    }

    /**
     * @param $value
     * @param $criteria_value
     * @return bool
     */
    public function notIn( $value , $criteria_value){
        return  !isset($criteria_value[$value]);
    }

    /**
     * @param $value1
     * @param $value2
     * @return bool
     */
    public function or($value1 , $value2){
        return ($value1 || $value2);
    }

    /**
     * @param $value1
     * @param $value2
     * @return bool
     */
    public function and($value1 , $value2){
        return ($value1 && $value2);
    }

    /**
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