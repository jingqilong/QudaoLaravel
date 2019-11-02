<?php
/**
 * 活动枚举
 */
namespace App\Enums;


class ProjectEnum extends BaseEnum
{

    public static $labels=[
        //项目对接  审核状态
        'SUBMIT'              => '已提交',
        'PASS'                => '审核通过',
        'NOPASS'              => '审核驳回',
    ];

    public static $status = [
        0 => 'SUBMIT',      //已提交
        1 => 'PASS',        //审核通过
        2 => 'NOPASS',      //审核失败
    ];

    //项目对接  审核状态
    const PASS              = 1;    //审核通过

    const NOPASS            = 2;    //审核失败

    const SUBMIT            = 0;    //待审核

    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getStatus(int $value){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : '';
    }
}