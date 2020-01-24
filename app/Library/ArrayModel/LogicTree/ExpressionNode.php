<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2020-01-24
 * Time: 11:59
 */

namespace App\Library\ArrayModel\LogicTree;


class ExpressionNode extends Node
{
    /**
     * properties : Keep the properties of node
     *
     * @var array
     */
    public $properties = [];

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->properties[$name];
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->properties[$name] = $value;
    }
}