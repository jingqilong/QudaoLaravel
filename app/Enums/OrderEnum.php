<?php
/**
 * 交易表枚举
 */
namespace App\Enums;


class OrderEnum extends BaseEnum
{
    public static $labels = [
        //交易状态
        'STATUSTRADING' => '待付款',
        'STATUSSUCCESS' => '已付款',
        'STATUSFAIL'    => '未付款',
        'STATUSCLOSE'   => '已关闭',
        //订单类型
        'MEMBERRECHARGE'=> '会员充值',
        'ACTIVITY'      => '参加活动',
        'PRIME'         => '精选生活',
        'SHOP'          => '商城',
    ];

    public static $status = [
        0 => 'STATUSTRADING',
        1 => 'STATUSSUCCESS',
        2 => 'STATUSFAIL',
        3 => 'STATUSCLOSE',
    ];

    public static $order_type = [
        1 => 'MEMBERRECHARGE',
        2 => 'ACTIVITY',
        3 => 'PRIME',
        4 => 'SHOP',
    ];

    //交易状态

    const STATUSTRADING        = 0;    //交易状态-待付款

    const STATUSSUCCESS        = 1;    //交易状态-已付款

    const STATUSFAIL           = 2;    //交易状态-未付款（付款失败）

    const STATUSCLOSE          = 3;    //交易状态-已关闭（已取消）

    //订单类型

    const MEMBERRECHARGE        = 1;    //订单类型-会员充值

    const ACTIVITY              = 2;    //订单类型-参加活动

    const PRIME                 = 3;    //订单类型-精选生活

    const SHOP                  = 4;    //订单类型-商城

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
    public static function getOrderType(int $value,$default = ''){
        return isset(self::$order_type[$value]) ? self::$labels[self::$order_type[$value]] : $default;
    }
}