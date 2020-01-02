<?php
/**
 * 活动报名报名状态枚举
 */
namespace App\Enums;


class ActivityRegisterStatusEnum extends BaseEnum
{
    //报名状态
    public static $labels = [
        'SUBMIT'        => '待支付',
        'EVALUATION'    => '待评价',
        'COMPLETED'     => '已完成',
        'CANCELED'      => '已取消',
    ];

    public static $status = [
        1 => 'SUBMIT',
        2 => 'EVALUATION',
        3 => 'COMPLETED',
        4 => 'CANCELED',
    ];

    // constants

    const SUBMIT            = 1;    //报名状态-待支付

    const EVALUATION        = 2;    //报名状态-待评价

    const COMPLETED         = 3;    //报名状态-已完成

    const CANCELED          = 4;    //报名状态-已取消

    /**
     * 获取身份标签
     * @param int $value
     * @return mixed|string
     */
    public static function getStatus($value){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : '';
    }
}