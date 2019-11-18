<?php
/**
 * 积分枚举
 */
namespace App\Enums;


class ScoreEnum extends BaseEnum
{
    public static $labels=[
        //积分类型状态
        'OPEN'      => '开启',
        'CLOSE'     => '关闭',
        //是否可提现
        'CANCASH'   => '可以提现',
        'NOCANCASH' => '不可提现',
        //积分操作
        'INCREASE'      => '增加',
        'EXPENSE'       => '消费',
        //是否最新
        'OLD'           => '历史',
        'LATEST'        => '最新的',
    ];

    public static $status = [
        0 => 'OPEN',
        1 => 'CLOSE'
    ];
    public static $cashing = [
        0 => 'CANCASH',
        1 => 'NOCANCASH'
    ];
    public static $action = [
        0 => 'INCREASE',
        1 => 'EXPENSE'
    ];
    public static $latest = [
        0 => 'OLD',
        1 => 'LATEST'
    ];

    // constants

    const OPEN          = 0;    //开启
    const CLOSE         = 1;    //关闭

    const CANCASH       = 0;    //可以提现
    const NOCANCASH     = 1;    //不可提现

    const INCREASE      = 0;    //增加
    const EXPENSE       = 1;    //消费

    const OLD           = 0;    //历史
    const LATEST        = 1;    //最新的

    /**
     * @param int $value        值
     * @param string $default   默认值
     * @return mixed|string
     */
    public static function getStatus(int $value,$default = ''){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : $default;
    }


    /**
     * @param int $value
     * @return mixed|string
     */
    public static function getAction(int $value){
        return isset(self::$action[$value]) ? self::$labels[self::$action[$value]] : '';
    }
    /**
     * @param int $value
     * @return mixed|string
     */
    public static function getCashing(int $value){
        return isset(self::$cashing[$value]) ? self::$labels[self::$cashing[$value]] : '';
    }
    /**
     * @param int $value
     * @return mixed|string
     */
    public static function getLatest(int $value){
        return isset(self::$latest[$value]) ? self::$labels[self::$latest[$value]] : '';
    }
}