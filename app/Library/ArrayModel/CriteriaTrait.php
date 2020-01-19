<?php


namespace App\Library\ArrayModel;


trait CriteriaTrait
{
    /**
     * @var array
     */
    private static $tokens_chars = [
        '32' => 'space',
        '33' => 'exclamation',
        '34' => 'double_quote',
        '37' => 'percent',
        '39' => 'single_quote',
        '40' => 'left_bracket',
        '41' => 'right_bracket',
        '42' => 'multiple',
        '43' => 'plus',
        '44' => 'comma',
        '45' => 'minus',
        '46' => 'dot',
        '47' => 'division',
        '60' => 'lt',
        '61' => 'eq',
        '62' => 'gt',
        '95' => 'underline'
    ];

    public static $tokens = [
        'true' => true,
        'false' => false,
        'null' => null,
        'eq' => 'eq',
        'lt' => 'lt',
        'lte' => 'lte',
        'gt' => 'gt',
        'gte' => 'gte',
        'neq' => 'neq',
        'in' => 'in',
        'like' => 'like',
        'or' => 'or',
        'and' => 'and',
        'is' => 'is',
        'not' => 'not',
    ];

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