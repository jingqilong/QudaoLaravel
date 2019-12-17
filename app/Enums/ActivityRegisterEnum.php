<?php
/**
 * 活动报名枚举
 */
namespace App\Enums;


class ActivityRegisterEnum extends BaseEnum
{
    //报名状态
    public static $labels = [
        'PENDING'       => '待审核',
        'SUBMIT'        => '待支付',
        'EVALUATION'    => '已支付',
        'COMPLETED'     => '已完成',
        'NOPASS'        => '未通过',
        'CANCELED'      => '已取消',
    ];

    public static $status = [
        1 => 'PENDING',
        2 => 'SUBMIT',
        3 => 'EVALUATION',
        4 => 'COMPLETED',
        5 => 'NOPASS',
        6 => 'CANCELED',
    ];

    // constants

    const PENDING           = 1;    //报名状态-待审核

    const SUBMIT            = 2;    //报名状态-待支付

    const EVALUATION        = 3;    //报名状态-待评价

    const COMPLETED         = 4;    //报名状态-已完成

    const NOPASS            = 5;    //报名状态-未通过

    const CANCELED          = 6;    //报名状态-已取消

    /**
     * 获取身份标签
     * @param int $value
     * @return mixed|string
     */
    public static function getStatus($value){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : '';
    }
}