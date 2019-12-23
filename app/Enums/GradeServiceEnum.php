<?php
/**
 * 成员等级枚举
 */
namespace App\Enums;


class GradeServiceEnum extends BaseEnum
{

    public static $labels=[
        //状态
        'NOTUSABLE'         => '不可用',
        'USABLE'            => '可用',
    ];

    //状态
    public static $status = [
        1 => 'NOTUSABLE',
        2 => 'USABLE',
    ];

    // 状态
    const NOTUSABLE         = 1;    //不可用

    const USABLE            = 2;    //可用


    /**
     * 获取状态
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getStatus(int $value,$default = ''){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : $default;
    }
}