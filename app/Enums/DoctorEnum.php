<?php
/**
 * 贷款枚举
 */
namespace App\Enums;


class DoctorEnum extends BaseEnum
{

    public static $labels=[
        //  审核状态
        'SUBMIT'              => '待审核',
        'PASS'                => '审核通过',
        'NOPASS'              => '审核驳回',
        'MAN'                 => '男',
        'WOMAN'               => '女',
    ];

    public static $status = [
        0 => 'SUBMIT',      //待审核
        1 => 'PASS',        //审核通过
        2 => 'NOPASS',      //审核失败
    ];


    public static $type = [
        1 => 'ONESELF',   //1本人预约
        2 => 'OTHERS',    //2推荐他人预约
    ];

    public static $sex = [
        1 => 'MAN',         //1男
        2 => 'WOMAN',       //2女
    ];

    //审核状态

    const PASS              = 1;    //审核通过

    const NOPASS            = 2;    //审核失败

    const SUBMIT            = 0;    //待审核

    //type  类型
    const MAN              = 1;    //男

    const WOMAN            = 2;    //女







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
    public static function getSex(int $value){
        return isset(self::$sex[$value]) ? self::$labels[self::$sex[$value]] : '';
    }
}