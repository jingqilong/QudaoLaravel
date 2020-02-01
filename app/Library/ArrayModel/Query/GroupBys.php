<?php


namespace App\Library\ArrayModel\Query;

use App\Library\ArrayModel\Abstracts\FieldList;
use InvalidArgumentException;

/**
 * Class GroupBys
 * @package App\Library\ArrayModel\Query
 */
class GroupBys extends FieldList
{

    /**
     * @param string[] ...$group_bys
     * @return GroupBys
     * @throws InvalidArgumentException
     */
    public static function init(...$group_bys){
        return parent::parse(...$group_bys);
    }

    /**
     * For debugging, import sql clause.
     *
     * @return string
     */
    public function _toSql(){
         return " GROUP BY " .  $this->fields_string;
    }
}