<?php
/**
 * 商户类型枚举
 */
namespace App\Enums;


class PrimeTypeEnum extends BaseEnum
{
    //商户类型
    public static $labels=[
        'FITNESS'   => '健身',
        'DINING'    => '餐饮',
        'HOTEL'     => '宾馆',
        //预约状态
        'RESERVATION'       => '待审核',
        'RESERVATIONOK'     => '预约成功',
        'RESERVATIONNO'     => '预约失败',
        'RESERVATIONCANCEL' => '预约取消',
    ];

    public static $type = [
        1 => 'FITNESS',
        2 => 'DINING',
        3 => 'HOTEL',
    ];
    #预约状态
    public static $reservation_status = [
        1 => 'RESERVATION',
        2 => 'RESERVATIONOK',
        3 => 'RESERVATIONNO',
        4 => 'RESERVATIONCANCEL',
    ];

    // constants

    const FITNESS       = 1;

    const DINING        = 2;

    const HOTEL         = 3;
    // 预约状态
    const RESERVATION       = 1;    //待审核
    const RESERVATIONOK     = 2;    //预约成功
    const RESERVATIONNO     = 3;    //预约失败
    const RESERVATIONCANCEL = 4;    //预约取消

    /**
     * @param int $value        值
     * @param string $default   默认值
     * @return mixed|string
     */
    public static function getType(int $value,$default = ''){
        return isset(self::$type[$value]) ? self::$labels[self::$type[$value]] : $default;
    }


    /**
     * 获取预约状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getReservationStatus(int $value){
        return isset(self::$reservation_status[$value]) ? self::$labels[self::$reservation_status[$value]] : '';
    }
}