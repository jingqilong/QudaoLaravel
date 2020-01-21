<?php


namespace App\Library\ArrayModel\Components;

use App\Library\ArrayModel\Abstracts\SortedList;
use App\Library\ArrayModel\FieldTrait;
use InvalidArgumentException;

/**
 * Class GroupBys
 * @package App\Library\ArrayModel
 */
class GroupBys extends SortedList
{

    use FieldTrait;

    /**
     * For debugging, import sql clause.
     * @var
     */
    public $group_by_string;

    /**
     * @param string ...$group_bys
     * @return SortedList
     * @throws InvalidArgumentException
     */
    public static function init(string ...$group_bys){
        $result = $columns =  [];
        foreach ($group_bys as $group_by){
            list($alias,$name) = self::extractField($group_by);
            $result[$alias][] = $name;
        }
        $instance = new static($result);
        $instance->group_by_string  = implode(',',$group_bys);
        return $instance;
    }

    /**
     * For debugging, import sql clause.
     *
     * @return string
     */
    public function _toSql(){
         return " GROUP BY " .  $this->group_by_string;
    }
}