<?php
/**
 * 活动评论枚举
 */
namespace App\Enums;


class ActivityCommentEnum extends BaseEnum
{

    public static $labels=[
        //评论状态
        'PENDING'   => '待审核',
        'PASS'      => '审核通过',
        'NOPASS'    => '审核未通过',
        //是否隐藏
        'DISPLAY'   => '正常',
        'HIDDEN'    => '隐藏',
    ];

    //评论状态
    public static $status = [
        1 => 'PENDING',
        2 => 'PASS',
        3 => 'NOPASS',
    ];
    //是否隐藏
    public static $hide = [
        0 => 'DISPLAY',
        1 => 'HIDDEN',
    ];
    // 评论状态
    const PENDING       = 1;    //待审核

    const PASS          = 2;    //审核通过

    const NOPASS        = 3;    //审核未通过
    // 是否隐藏
    const DISPLAY       = 0;    //显示

    const HIDDEN        = 1;    //隐藏

    /**
     * 获取评论状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getStatus(int $value){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : '';
    }
}