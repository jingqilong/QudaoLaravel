<?php
/**
 * 商城订单枚举
 */
namespace App\Enums;


class ShopOrderEnum extends BaseEnum
{
    public static $labels = [
        //订单状态
        'CANCELED'      => '已取消',
        'PAYMENT'       => '待支付',
        'SHIP'          => '待发货',
        'SHIPPED'       => '已发货',
        'RECEIVED'      => '已完成',
        //收货方式
        'BY_MAIL'       => '收费邮寄',
        'COLLECT'       => '快递到付',
        'ABHOLUNG'      => '上门自提',
    ];

    public static $status = [
        0 => 'CANCELED',
        1 => 'PAYMENT',
        2 => 'SHIP',
        3 => 'SHIPPED',
        4 => 'RECEIVED',
    ];

    public static $receive_method = [
        1 => 'BY_MAIL',
        2 => 'COLLECT',
        3 => 'ABHOLUNG'
    ];

    //订单状态

    const CANCELED          = 0;    //订单状态-已取消

    const PAYMENT           = 1;    //订单状态-待支付

    const SHIP              = 2;    //订单状态-待发货

    const SHIPPED           = 3;    //订单状态-已发货

    const RECEIVED          = 4;    //订单状态-已收货

    //收货方式

    const BY_MAIL           = 1;    //收货方式-收费邮寄

    const COLLECT           = 2;    //收货方式-快递到付

    const ABHOLUNG          = 3;    //收货方式-上门自提

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
    public static function getReceiveMethod(int $value,$default = ''){
        return isset(self::$receive_method[$value]) ? self::$labels[self::$receive_method[$value]] : $default;
    }
}