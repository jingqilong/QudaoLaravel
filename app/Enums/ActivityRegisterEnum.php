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
        'EVALUATION'    => '待评价',
        'COMPLETED'     => '已完成',
        'NOPASS'        => '未通过',
        'CANCELED'      => '已取消',
    ];

    // constants

    const PENDING           = 1;    //报名状态-待审核

    const SUBMIT            = 2;    //报名状态-待支付

    const EVALUATION        = 3;    //报名状态-待评价

    const COMPLETED         = 4;    //报名状态-已完成

    const NOPASS            = 5;    //报名状态-未通过

    const CANCELED          = 6;    //报名状态-已取消

}