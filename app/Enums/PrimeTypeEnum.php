<?php
/**
 * 商户类型枚举
 */
namespace App\Enums;


class PrimeTypeEnum extends BaseEnum
{
    //商户类型
    public static $labels=[
        'FITNESS'   => '健身',
        'DINING'    => '餐饮',
        'HOTEL'     => '宾馆',
    ];

    public static $type = [
        1 => 'FITNESS',
        2 => 'DINING',
        3 => 'HOTEL',
    ];

    // constants

    const FITNESS       = 1;

    const DINING        = 2;

    const HOTEL         = 3;

    /**
     * 获取三方绑定表类型label
     * @param int $value        值
     * @param string $default   默认值
     * @return mixed|string
     */
    public static function getType(int $value,$default = ''){
        return isset(self::$type[$value]) ? self::$labels[self::$type[$value]] : $default;
    }
}