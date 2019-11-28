<?php
/**
 * 贷款枚举
 */
namespace App\Enums;


class CommentsEnum extends BaseEnum
{

    public static $labels=[
        //  审核状态
        'SUBMIT'              => '待审核',
        'PASS'                => '审核通过',
        'NOPASS'              => '审核驳回',
        'SHOP'                => '商城',
        'ACTIVITY'            => '活动',
        'ACTIVITE'            => '显示',
        'HIDDEN'              => '隐藏',
    ];

    public static $status = [
        1 => 'SUBMIT',      //待审核
        2 => 'PASS',        //审核通过
        3 => 'NOPASS',      //审核失败
    ];


    public static $type = [
        1 => 'SHOP',         //1商城
        2 => 'ACTIVITY',     //2活动
    ];

    public static $hidden = [
        0 => 'ACTIVITE',         //显示
        1 => 'HIDDEN',           //隐藏
    ];

    //审核状态

    const PASS              = 2;    //审核通过

    const NOPASS            = 3;    //审核失败

    const SUBMIT            = 1;    //待审核

    //type  类型
    const SHOP              = 1;    //商城

    const ACTIVITY          = 2;    //活动

    //sex  类型
    const ACTIVITE          = 0;    //显示

    const HIDDEN            = 1;    //隐藏







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
    public static function getHidden (int $value){
        return isset(self::$hidden[$value]) ? self::$labels[self::$hidden [$value]] : '';
    }
}