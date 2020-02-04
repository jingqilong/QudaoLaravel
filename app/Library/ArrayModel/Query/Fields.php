<?php

namespace App\Library\ArrayModel\Query;

use App\Library\ArrayModel\Abstracts\FieldList;
use InvalidArgumentException;

/**
 * Class Fields
 * @package App\Library\ArrayModel\Query
 * @author Bardeen
 */
class Fields extends FieldList
{

    /**
     * @param string[] ...$fields
     * @return Fields
     * @throws InvalidArgumentException
     * @static
     */
    public static function newFields(...$fields){
        return parent::newFields(...$fields);
    }

    /**
     * For debugging , import sql clause
     *
     * @return string
     */
    public function _toSql(){
        return $this->fields_string;
    }

    /**
     * @desc Give the default value when the fields is empty.
     * @param null $alias
     * @return array|mixed
     */
    public function getFields($alias = null)
    {
        $fields = parent::getFields($alias);
        if(0==count($fields)){
            $fields = ['*'];
        }
        return $fields;
    }

}