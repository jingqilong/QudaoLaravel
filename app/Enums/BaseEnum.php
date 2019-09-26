<?php
/**
 * Created by PhpStorm.
 * User: Bardo
 * Date: 2018-06-06
 * Time: 12:38
 */

namespace App\Enums;


use Nasyrov\Laravel\Enums\Enum;

abstract class BaseEnum extends Enum
{
    /**
     * Get the enum label.
     * @param mixed $value
     * @return string
     */
    public static function getLabel($value)
    {
        $key=static::constants()->search($value, false);
        //可以先把labels放到类中，也可以放到资源文件中。
        $caller_class = get_called_class();
        if (isset($caller_class::$labels)){
            if (isset($caller_class::$labels[$key]))
                return $caller_class::$labels[$key];
            if (isset($caller_class::$labels['_DEFAULT']))
                return $caller_class::$labels['_DEFAULT'];
        }
        // Place your translation strings in `resources/lang/en/enum.php`
        return trans(sprintf('enum.%s', strtolower($key)));

    }

    /**
     * Get the enum labels.
     *
     * @return array
     */
    public static function labels()
    {
        return static::constants()
            ->flip()
            ->map(function ($key) {
                // Place your translation strings in `resources/lang/en/enum.php`
                return trans(sprintf('enum.%s', strtolower($key)));
            })
            ->all();
    }

    /**
     * 获取标签数值记录数组
     * @return array
     */
    public static function getLabels(){
        $keys = self::keys();
        $arr  = [];
        foreach($keys as $k => $v){
            $arr[$k]['type'] = (self::$v())->getValue();
            $arr[$k]['name'] = self::getLabel((self::$v())->getValue());
        }
        //array_unshift($arr, ['type' => '', 'name' => '请选择']);
        return $arr;
    }

    /**
     * @param $const
     * @return bool
     */
    public static function isset($const){
        return isset(self::$labels[$const]);
    }

    /**
     * @param $const
     * @return string
     */
    public static function getConst($const){
        $value = self::values();
        return $value[$const].'';
    }
}