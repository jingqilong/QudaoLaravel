<?php
/**
 * 公共性别枚举
 */
namespace App\Enums;


class CommonGenderEnum extends BaseEnum
{

    public static $labels=[
        'GENTLEMEN'     => '先生',
        'LADY'          => '女士'
    ];

    public static $status = [
        1 => 'GENTLEMEN',
        2 => 'LADY',
    ];

    #banner模块
    const GENTLEMEN     = 1;    //先生

    const LADY          = 2;    //女士

//    /**
//     * 获取展示状态
//     * @param $value
//     * @param string $default
//     * @return mixed|string
//     */
//    public static function getLabel($value, $default = ''){
//        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : $default;
//    }
}