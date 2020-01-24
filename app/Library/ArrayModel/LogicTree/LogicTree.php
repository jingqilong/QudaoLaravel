<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2020-01-24
 * Time: 11:22
 */

namespace App\Library\ArrayModel\LogicTree;

use App\Library\ArrayModel\LogicTree\AndNode;
use App\Library\ArrayModel\LogicTree\OrNode;
use App\Library\ArrayModel\LogicTree\Node;

class LogicTree extends OrBrackets
{
    /**
     * Keep the location of the operatiron
     *
     * @var $current_position
     */
    public $current_position;

    /**
     * @return $this|Node
     */
    public function getAncestor(){
        return $this;
    }



}