<?php
/**
 * 交易表枚举
 */
namespace App\Enums;


class TradeEnum extends BaseEnum
{
    //交易状态
    public static $labels = [
        'STATUSTRADING'     => '正在交易',
        'STATUSSUCCESS'     => '交易成功',
        'STATUSFAIL'        => '交易失败',
        #交易方式
        'WECHANT'           => '微信支付',
        'SCORE'             => '积分支付',
        'UNION'             => '银联支付',
    ];

    public static $status = [
        0 => 'STATUSTRADING',
        1 => 'STATUSSUCCESS',
        2 => 'STATUSFAIL',
    ];

    public static $trade_method = [
        1 => 'WECHANT',
        2 => 'SCORE',
        3 => 'UNION',
    ];

    // constants

    const STATUSTRADING        = 0;    //交易状态-正在交易

    const STATUSSUCCESS        = 1;    //交易状态-交易成功

    const STATUSFAIL           = 2;    //交易状态-交易失败


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
    public static function getTradeMethod(int $value,$default = ''){
        return isset(self::$trade_method[$value]) ? self::$labels[self::$trade_method[$value]] : $default;
    }
}