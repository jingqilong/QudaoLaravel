<?php
/**
 * 活动枚举
 */
namespace App\Enums;


class LoanEnum extends BaseEnum
{

    public static $labels=[
        //房产  审核状态
        'SUBMITTED'           => '已提交',
        'INREVIEW'            => '审核中',
        'PASS'                => '审核通过',
        'FAILURE'             => '审核失败',
        'DELETE'              => '已删除',
        'ONESELF'             => '本人预约',
        'OTHERS'              => '推荐他人预约',
    ];

    public static $status = [
        1 => 'SUBMITTED',   //已提交
        2 => 'INREVIEW',    //审核中
        3 => 'PASS',        //审核通过
        4 => 'FAILURE',     //审核失败
        9 => 'DELETE',      //已删除
    ];

    public static $type = [
        1 => 'ONESELF',   //1本人预约
        2 => 'OTHERS',    //2推荐他人预约
    ];

    //房产 审核状态
    const SUBMITTED         = 1;    //已提交

    const INREVIEW          = 2;    //审核中

    const PASS              = 3;    //审核通过

    const FAILURE           = 4;    //审核失败

    const DELETE            = 9;    //已删除

    //type  类型
    const ONESELF           = 1;    //本人预约

    const OTHERS            = 2;    //推荐他人预约

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
}