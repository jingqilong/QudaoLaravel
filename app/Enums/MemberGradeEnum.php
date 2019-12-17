<?php
/**
 * 成员等级枚举
 */
namespace App\Enums;


class MemberGradeEnum extends BaseEnum
{

    public static $labels=[
        //等级状态
        'ENABLE'            => '启用',
        'CLOSE'             => '关闭',
        //是否可购买
        'CANNOTBUY'         => '非购买',
        'CANBUY'            => '可以购买',
    ];

    //状态
    public static $status = [
        0 => 'ENABLE',
        1 => 'CLOSE',
    ];

    //是否可购买
    public static $is_buy = [
        0 => 'CANNOTBUY',
        1 => 'CANBUY',
    ];

    // 状态
    const ENABLE            = 0;    //启用

    const CLOSE             = 1;    //关闭

    //是否可购买
    const CANNOTBUY         = 0;    //非购买

    const CANBUY            = 1;    //可以购买


    /**
     * 获取状态
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getStatus(int $value,$default = ''){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : $default;
    }

    /**
     * 获取是否可购买
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getIsBuy(int $value,$default = ''){
        return isset(self::$is_buy[$value]) ? self::$labels[self::$is_buy[$value]] : $default;
    }

}