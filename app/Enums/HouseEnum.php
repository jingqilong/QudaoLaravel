<?php
/**
 * 房产枚举
 */
namespace App\Enums;


use http\Encoding\Stream\Deflate;

class HouseEnum extends BaseEnum
{

    public static $labels=[
        //租期
        'HOUR'          => '小时',
        'DAY'           => '天',
        'WEEK'          => '周',
        'MONTH'         => '月',
        'YEAR'          => '年',
        //装修
        'GENERAL'       => '普装',
        'DELICATE'      => '精装',
        'LUXURY'        => '豪装',
        //房产类别
        'RESIDENCE'     => '住宅',
        'SHOP'          => '商铺',
        'OFFICE'        => '办公楼',
        'WORKSHOP'      => '厂房/仓库',
        //发布方
        'PERSON'        => '个人',
        'PLATFORM'      => '平台',
        //状态
        'PENDING'       => '待审核',
        'PASS'          => '招租中',
        'NOPASS'        => '未过审',
        'RENTED'        => '已出租',
        //预约状态
        'RESERVATION'   => '正在预约',
        'RESERVATIONOK' => '预约成功',
        'RESERVATIONNO' => '预约失败',
    ];

    #租期
    public static $tenancy = [
        1 => 'HOUR',
        2 => 'DAY',
        3 => 'WEEK',
        4 => 'MONTH',
        5 => 'YEAR'
    ];

    #装修
    public static $decoration = [
        1 => 'GENERAL',
        2 => 'DELICATE',
        3 => 'LUXURY',
    ];

    #房产类别
    public static $category = [
        1 => 'RESIDENCE',
        2 => 'SHOP',
        3 => 'OFFICE',
        4 => 'WORKSHOP',
    ];

    #发布方
    public static $publisher = [
        1 => 'PERSON',
        2 => 'PLATFORM',
    ];

    #状态
    public static $status = [
        1 => 'PENDING',
        2 => 'PASS',
        3 => 'NOPASS',
        4 => 'RENTED',
    ];

    #预约状态
    public static $reservation_status = [
        1 => 'RESERVATION',
        2 => 'RESERVATIONOK',
        3 => 'RESERVATIONNO',
    ];

    // 租期
    const HOUR          = 1;    //时
    const DAY           = 2;    //天
    const WEEK          = 1;    //周
    const MONTH         = 2;    //月
    const YEAR          = 1;    //年

    // 装修
    const GENERAL       = 1;    //普装
    const DELICATE      = 2;    //精装
    const LUXURY        = 3;    //豪装

    // 房产类别
    const RESIDENCE     = 1;    //住宅
    const SHOP          = 2;    //商铺
    const OFFICE        = 3;    //办公楼
    const WORKSHOP      = 4;    //厂房/仓库

    // 房产类别
    const PERSON        = 1;    //个人
    const PLATFORM      = 2;    //平台

    // 状态
    const PENDING       = 1;    //审核中
    const PASS          = 2;    //通过
    const NOPASS        = 3;    //未通过
    const RENTED        = 4;    //已出租

    // 预约状态
    const RESERVATION   = 1;    //预约中
    const RESERVATIONOK = 2;    //预约成功
    const RESERVATIONNO = 3;    //预约失败


    /**
     * 获取租期label
     * @param int $value
     * @return mixed|string
     */
    public static function getTenancy(int $value){
        return isset(self::$tenancy[$value]) ? self::$labels[self::$tenancy[$value]] : '';
    }

    /**
     * 获取装修label
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getDecoration(int $value,$default = ''){
        return isset(self::$decoration[$value]) ? self::$labels[self::$decoration[$value]] : $default;
    }

    /**
     * 获取房产类别label
     * @param int $value
     * @return mixed|string
     */
    public static function getCategory(int $value){
        return isset(self::$category[$value]) ? self::$labels[self::$category[$value]] : '';
    }

    /**
     * 获取发布方label
     * @param int $value
     * @return mixed|string
     */
    public static function getPublisher(int $value){
        return isset(self::$publisher[$value]) ? self::$labels[self::$publisher[$value]] : '';
    }

    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getStatus(int $value){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : '';
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