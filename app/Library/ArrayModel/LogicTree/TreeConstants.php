<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2020-01-24
 * Time: 12:12
 */

namespace App\Library\ArrayModel\LogicTree;


class TreeConstants
{
    /**
     * Logic Operator "and"
     */
    const LOGIC_AND = 'And';

    /**
     * Logic Operator "or"
     */
    const LOGIC_OR = 'Or';

    /**
     * node type: node of brackets
     */
    const NODE_TYPE_BRACKETS = 0;

    /**
     * node type: node of expression
     */
    const NODE_TYPE_EXPRESSION = 1;
}