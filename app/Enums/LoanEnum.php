<?php
/**
 * 贷款枚举
 */
namespace App\Enums;


class LoanEnum extends BaseEnum
{

    public static $labels=[
        //房产  审核状态
        'SUBMIT'              => '待审核',
        'PASS'                => '审核通过',
        'NOPASS'              => '审核驳回',
        'CANCEL'              => '预约取消',
        'ONESELF'             => '本人预约',
        'OTHERS'              => '他人推荐预约',
        'MILLION'             => '100万 - 200万',
        'TWOMILLION'          => '200万 - 300万',
        'THREEMILLION'        => '300万 - 500万',
        'FOURMILLION'         => '500万 - 1000万',
        'FIVEMILLION'         => '1000万以上',
        'PLATFORM'            => '平台预约',
        'ACTIVITY'            => '活动预约',
    ];

    public static $status = [
        0 => 'SUBMIT',      //待审核
        1 => 'PASS',        //审核通过
        2 => 'NOPASS',      //审核失败
        3 => 'CANCEL',      //取消预约
    ];

    public static $price = [
        1 => 'MILLION',         //100万 - 200万
        2 => 'TWOMILLION',      //200万 - 300万
        3 => 'THREEMILLION',    //300万 - 500万
        4 => 'FOURMILLION',     //500万 - 1000万
        5 => 'FIVEMILLION',     //1000万以上
    ];

    public static $type = [
        1 => 'ONESELF',   //1本人预约
        2 => 'OTHERS',    //2推荐他人预约
    ];

    public static $appointment = [
        0 => 'PLATFORM',    //0平台预约
        1 => 'ACTIVITY',    //1活动预约
    ];

    //贷款 审核状态

    const SUBMIT            = 0;    //待审核

    const PASS              = 1;    //审核通过

    const NOPASS            = 2;    //审核失败

    const CANCEL            = 3;    //取消预约

    //type  类型
    const ONESELF           = 1;    //本人预约

    const OTHERS            = 2;    //推荐他人预约

    //贷款 审核状态
    const MILLION           = 1;    //100万 - 200万

    const TWOMILLION        = 2;    //200万 - 300万

    const THREEMILLION      = 3;    //300万 - 500万

    const FOURMILLION       = 4;    //500万 - 1000万

    const FIVEMILLION       = 5;    //1000万以上

    const PLATFORM          = 0;    //平台预约

    const ACTIVITY          = 1;    //活动预约


    /**
     * 获取状态label
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getStatus($value, $default = ''){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : $default;
    }
    /**
     * 获取状态label
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getType($value, $default = ''){
        return isset(self::$type[$value]) ? self::$labels[self::$type[$value]] : $default;
    }
    /**
     * 获取状态label
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getPrice($value, $default = ''){
        return isset(self::$price[$value]) ? self::$labels[self::$price[$value]] : $default;
    }

    /**
     * 获取状态label
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getAppointment($value, $default = ''){
        return isset(self::$appointment[$value]) ? self::$labels[self::$appointment[$value]] : $default;
    }
}