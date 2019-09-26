<?php
/**
 * 交易表枚举
 */
namespace App\Enums;


class OrderEnum extends BaseEnum
{
    //交易状态
    public static $labels = [
        0 => 'STATUSTRADING',
        1 => 'STATUSSUCCESS',
        2 => 'STATUSFAIL',
        3 => 'STATUSCLOSE',
    ];

    // constants

    const STATUSTRADING        = 0;    //交易状态-待付款

    const STATUSSUCCESS        = 1;    //交易状态-已付款

    const STATUSFAIL           = 2;    //交易状态-未付款（付款失败）

    const STATUSCLOSE          = 3;    //交易状态-已关闭（已取消）

}