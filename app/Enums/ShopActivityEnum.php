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
        'SCOREEXCHANGE' => '分类页积分兑换栏目',
        'GOODRECOMMEND' => '首页的好物推荐列表',
        'HOMESHOW'      => '首页积分推荐广告位',
    ];

    public static $status = [
        1 => 'DISABLE',
        2 => 'OPEN',
    ];

    public static $type = [
        1 => 'SCOREEXCHANGE',
        2 => 'GOODRECOMMEND',
        3 => 'HOMESHOW',
    ];

    //状态

    const DISABLE           = 1;    //禁用

    const OPEN              = 2;    //开启

    //类型

    const SCOREEXCHANGE     = 1;    //积分兑换

    const GOODRECOMMEND     = 2;    //好物推荐

    const HOMESHOW          = 3;    //首页展示

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