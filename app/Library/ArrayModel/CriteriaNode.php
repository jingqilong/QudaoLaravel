<?php


namespace App\Library\ArrayModel;

/**
 * Class CriteriaNode
 * @package App\Library\ArrayModel
 */
class CriteriaNode extends Tree
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
     * @param $criteria_tokens
     * @param int $pos
     * @param int $node_id
     * @param null $parent_node
     * @return $this
     */
    public static function init($criteria_tokens, $pos=0, $node_id = 0,$parent_node = null){
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
     * @param $criteria_tokens
     * @param $pos
     * @return bool
     */
    public function parseEquation($criteria_tokens,$pos){
        return true;
    }



}