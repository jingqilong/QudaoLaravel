<?php

namespace App\Library\ArrayModel;

/**
 * Class Criteria
 * @package App\Library\ArrayModel
 */
class Criteria
{

    use CriteriaTrait;

    /**
     * @param $char
     * @return mixed|string
     */
    private static function getTokenKey($char){
        $code = ord($char);
        if((48<= $code)&&(57>=$code)){
            return 'number';
        }
        if((65<= $code)&&(90>=$code)){
            return 'up_latter';
        }
        if((97<= $code)&&(122>=$code)){
            return 'lower_latter';
        }
        if(isset(self::$tokens_chars[$code])){
            return self::$tokens_chars[$code];
        }
        return '';
    }

    /**
     * @param $criteria
     * @return array
     */
    public static function tokenize($criteria){
        $result = $tokens =  [];
        for($i=0,$j=strlen($criteria);$i<$j;$i++){
            $token = self::getTokenKey($criteria[$i]);
            $result[$i]['char'] = $criteria[$i];
            $result[$i]['token'] = $token;
            $tokens[$token][] = $i;
        }
        return ['index' => $result,'tokens' => $tokens];
    }

    /**
     * @param $criteria
     * @return CriteriaNode
     */
    public static function init($criteria){
        $criteria_tokens = self::tokenize($criteria);
        $criteria_tree = CriteriaNode::init($criteria_tokens);
        return $criteria_tree;
    }
}