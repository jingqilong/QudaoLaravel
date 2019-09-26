<?php
/**
 * 支付方式枚举
 */
namespace App\Enums;


class PayMethodEnum extends BaseEnum
{
    //短信类型
    public static $labels = [
        1 => 'WECHATPAY',
    ];

    // constants

    const WECHATPAY         = 1;    //微信支付

}