<?php


namespace App\Library\ArrayModel;


use InvalidArgumentException;

Trait FieldTrait
{
    /**
     * @param $field
     * @return string
     */
    public function extractAlias($field){
        return strstr($field, '.', true);
    }

    /**
     * @param $field
     * @return array
     */
    public static function extractField($field){
        $exploded = explode(".", preg_replace('/\s+/', '', $field));
        if(2 !== count($exploded)){
            throw new InvalidArgumentException("incorrect value for '$field'! should be 'table.field'");
        }
        return  $exploded;
    }



}