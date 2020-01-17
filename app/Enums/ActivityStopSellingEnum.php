<?php
/**
 * 活动售票状态枚举
 */
namespace App\Enums;


class ActivityStopSellingEnum extends BaseEnum
{
    //审核状态
    public static $labels = [
        'NORMAL_SELLING'    => '正常出售',
        'STOP_SELLING'      => '停止出售',
    ];

    public static $stop_selling = [
        0 => 'NORMAL_SELLING',
        1 => 'STOP_SELLING',
    ];

    // constants

    const NORMAL_SELLING    = 0;    //正常出售

    const STOP_SELLING      = 1;    //停止出售

    /**
     * 获取身份标签
     * @param int $value
     * @return mixed|string
     */
    public static function getStopSelling($value){
        return isset(self::$stop_selling[$value]) ? self::$labels[self::$stop_selling[$value]] : '';
    }
}