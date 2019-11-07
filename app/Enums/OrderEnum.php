<?php
/**
 * 交易表枚举
 */
namespace App\Enums;


class OrderEnum extends BaseEnum
{
    //交易状态
    public static $labels = [
        'STATUSTRADING' => '待付款',
        'STATUSSUCCESS' => '已付款',
        'STATUSFAIL'    => '未付款',
        'STATUSCLOSE'   => '已关闭',
    ];

    public static $status = [
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
    /**
     * @param int $value        值
     * @param string $default   默认值
     * @return mixed|string
     */
    public static function getStatus(int $value,$default = ''){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : $default;
    }
}