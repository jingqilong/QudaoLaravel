<?php
/**
 * 商城活动枚举
 */
namespace App\Enums;


class ShopActivityEnum extends BaseEnum
{
    public static $labels = [
        //状态
        'DISABLE'       => '禁用',
        'OPEN'          => '开启',
        //类型
        'SCOREEXCHANGE' => '积分兑换',
        'GOODRECOMMEND' => '好物推荐（首页）',
    ];

    public static $status = [
        1 => 'DISABLE',
        2 => 'OPEN',
    ];

    public static $type = [
        1 => 'SCOREEXCHANGE',
        2 => 'GOODRECOMMEND',
    ];

    //状态

    const DISABLE           = 1;    //禁用

    const OPEN              = 2;    //开启

    //类型

    const SCOREEXCHANGE     = 1;    //积分兑换

    const GOODRECOMMEND     = 2;    //好物推荐（首页）

    /**
     * @param int $value        值
     * @param string $default   默认值
     * @return mixed|string
     */
    public static function getStatus(int $value,$default = ''){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : $default;
    }
    /**
     * @param int $value        值
     * @param string $default   默认值
     * @return mixed|string
     */
    public static function getType(int $value,$default = ''){
        return isset(self::$type[$value]) ? self::$labels[self::$type[$value]] : $default;
    }
}