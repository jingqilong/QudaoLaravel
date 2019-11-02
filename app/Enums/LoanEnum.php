<?php
/**
 * 贷款枚举
 */
namespace App\Enums;


class LoanEnum extends BaseEnum
{

    public static $labels=[
        //房产  审核状态
        'SUBMIT'              => '已提交',
        'PASS'                => '审核通过',
        'NOPASS'              => '审核驳回',
        'ONESELF'             => '本人预约',
        'OTHERS'              => '推荐他人预约',
        'MILLION'             => '一百万',
        'TWOMILLION'          => '二百万',
        'THREEMILLION'        => '三百万',
        'FOURMILLION'         => '四百万',
        'FIVEMILLION'         => '五百万',
    ];

    public static $status = [
        0 => 'SUBMIT',      //已提交
        1 => 'PASS',        //审核通过
        2 => 'NOPASS',      //审核失败
    ];

    public static $price = [
        1 => 'MILLION',         //一百万
        2 => 'TWOMILLION',      //二百万
        3 => 'THREEMILLION',    //三百万
        4 => 'FOURMILLION',     //四百万
        5 => 'FIVEMILLION',     //五百万
    ];

    public static $type = [
        1 => 'ONESELF',   //1本人预约
        2 => 'OTHERS',    //2推荐他人预约
    ];

    //贷款 审核状态

    const PASS              = 1;    //审核通过

    const NOPASS            = 2;    //审核失败

    const SUBMIT            = 0;    //待审核

    //type  类型
    const ONESELF           = 1;    //本人预约

    const OTHERS            = 2;    //推荐他人预约

    //贷款 审核状态
    const MILLION           = 1;    //一百万

    const TWOMILLION        = 2;    //二百万

    const THREEMILLION      = 3;    //三百万

    const FOURMILLION       = 4;    //四百万

    const FIVEMILLION       = 5;    //五百万


    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getStatus(int $value){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : '';
    }
    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getType(int $value){
        return isset(self::$type[$value]) ? self::$labels[self::$type[$value]] : '';
    }
    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getPrice(int $value){
        return isset(self::$price[$value]) ? self::$labels[self::$price[$value]] : '';
    }
}