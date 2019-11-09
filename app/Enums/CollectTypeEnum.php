<?php
/**
 * 收藏类型枚举
 */
namespace App\Enums;


class CollectTypeEnum extends BaseEnum
{
    public static $labels=[
        //收藏类型
        'ACTIVITY'      => '精选活动',
        'SHOP'          => '商品',
        'HOUSE'         => '房产',
        'PRIME'         => '精选生活',
    ];

    public static $type = [
        1 => 'ACTIVITY',
        2 => 'SHOP',
        3 => 'HOUSE',
        4 => 'PRIME',
    ];

    // constants

    const ACTIVITY      = 1;

    const SHOP          = 2;

    const HOUSE         = 3;

    const PRIME         = 4;

    /**
     * @param int $value        值
     * @param string $default   默认值
     * @return mixed|string
     */
    public static function getType(int $value,$default = ''){
        return isset(self::$type[$value]) ? self::$labels[self::$type[$value]] : $default;
    }

}