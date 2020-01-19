<?php


namespace App\Library\ArrayModel;

/**
 * Class CriteriaNode
 * @package App\Library\ArrayModel
 */
class Criteria extends Tree
{
    use CriteriaTrait;

    public $_node_id = 0;

    public $_logic = null;

    public $_alias  = '';

    public $_field  = '';

    public $_operator  = '';

    public $_criteria_value  = '';

    public $_children = [];

    /**
     * @param $criteria
     * @param int $pos
     * @param int $node_id
     * @param null $parent_node
     * @return $this
     */
    public static function init($criteria, $pos=0, $node_id = 0,$parent_node = null){
        $criteria_tokens = self::tokenize($criteria);
        $instance = new static();
        $instance->_parent_node = $parent_node;
        $instance->_node_id = $node_id;

        if(!isset($criteria_tokens['tokens'])
            &&(!isset($criteria_tokens['tokens']['left_bracket'][$node_id]))){
            $instance->parseEquation($criteria_tokens,$pos);
            return $instance;
        }
    }

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
     * @param $criteria_tokens
     * @param $pos
     * @return bool
     */
    public function parseEquation($criteria_tokens,$pos){
        return true;
    }



}