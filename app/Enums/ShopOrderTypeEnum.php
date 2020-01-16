<?php
/**
 * 商城订单类型枚举
 */
namespace App\Enums;


class ShopOrderTypeEnum extends BaseEnum
{
    public static $labels = [
        'ORDINARY'      => '普通订单',
        'NEGOTIABLE'    => '面议订单',
    ];

    public static $order_type = [
        0 => 'ORDINARY',
        1 => 'NEGOTIABLE',
    ];
    //订单状态

    const ORDINARY          = 0;    //普通订单

    const NEGOTIABLE        = 1;    //面议订单

    /**
     * @param int $value        值
     * @param string $default   默认值
     * @return mixed|string
     */
    public static function getOrderType(int $value,$default = ''){
        return isset(self::$order_type[$value]) ? self::$labels[self::$order_type[$value]] : $default;
    }
}