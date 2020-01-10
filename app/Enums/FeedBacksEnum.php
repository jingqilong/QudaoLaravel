<?php
/**
 * 贷款枚举
 */
namespace App\Enums;


class FeedBacksEnum extends BaseEnum
{

    public static $labels=[
        //  审核状态
        'SUBMIT'            => '已提交',
        'MANAGE'            => '已处理',
        'CLOSE'             => '已关闭',
        'MEMBER'            => '成员用户',
        'OA'                => '员工用户',
    ];

    //操作人类型
    public static $operator_type = [
        0 => 'MEMBER',      //成员用户
        1 => 'OA',          //员工用户
    ];


    public static $status = [
        0 => 'SUBMIT',      //0已提交
        1 => 'MANAGE',      //1已处理
        2 => 'CLOSE',       //2已关闭
    ];


    //操作人类型

    const MEMBER            = 0;    //成员用户

    const OA                = 1;    //员工用户

    //状态
    const SUBMIT            = 0;    //已提交

    const MANAGE            = 1;    //已处理

    const CLOSE             = 2;    //已关闭








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
    public static function getOperatorType(int $value){
        return isset(self::$operator_type[$value]) ? self::$labels[self::$operator_type[$value]] : '';
    }
}