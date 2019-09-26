<?php
/**
 * 交易表枚举
 */
namespace App\Enums;


class TradeEnum extends BaseEnum
{
    //交易状态
    public static $labels = [
        0 => 'STATUSTRADING',
        1 => 'STATUSSUCCESS',
        2 => 'STATUSFAIL',
    ];

    // constants

    const STATUSTRADING        = 0;    //交易状态-正在交易

    const STATUSSUCCESS        = 1;    //交易状态-交易成功

    const STATUSFAIL           = 2;    //交易状态-交易失败

}