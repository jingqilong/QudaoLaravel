<?php
/**
 * 活动枚举
 */
namespace App\Enums;


class ProjectEnum extends BaseEnum
{

    public static $labels=[
        //项目对接  审核状态
        'SUBMITTED'           => '已提交',
        'INREVIEW'            => '审核中',
        'PASS'                => '审核通过',
        'FAILURE'             => '审核失败',
        'DELETE'              => '已删除',
    ];

    public static $status = [
        1 => 'SUBMITTED',   //已提交
        2 => 'INREVIEW',    //审核中
        3 => 'PASS',        //审核通过
        4 => 'FAILURE',     //审核失败
        9 => 'DELETE',      //已删除
    ];

    //项目对接  审核状态
    const SUBMITTED         = 1;    //已提交

    const INREVIEW          = 2;    //审核中

    const PASS              = 3;    //审核通过

    const FAILURE           = 4;    //审核失败

    const DELETE            = 9;    //已删除

    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getStatus(int $value){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : '';
    }
}