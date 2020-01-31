<?php
/**
 * Created by PhpStorm.
 * User: Bardeen
 * Date: 2020-01-24
 * Time: 12:12
 */

namespace App\Library\ArrayModel\LogicTree;

/**
 * Keep the constants of the library
 * Class TreeConstants
 * @package App\Library\ArrayModel\LogicTree
 */
class TreeConstants
{
    /**
     * Logic Operator "left"
     */
    const LOGIC_LEFT = 'left';

    /**
     * Logic Operator "and"
     */
    const LOGIC_AND = 'and';

    /**
     * Logic Operator "or"
     */
    const LOGIC_OR = 'or';

    /**
     * Node which contains the equation such as a=b
     */
    const NODE_TYPE_EXPRESSION   = 0;

    /**
     * Node which only has children(brackets node)
     */
    const NODE_TYPE_AGGREGATE    = 1;
}