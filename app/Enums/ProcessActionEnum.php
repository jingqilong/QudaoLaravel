<?php
/**
 * 流程动作枚举
 */
namespace App\Enums;


class ProcessActionEnum extends BaseEnum
{

    public static $labels=[
        //状态
        'ENABLE'        => '启用',
        'DISABLED'      => '禁用',
    ];

    public static $status = [
        1 => 'ENABLE',
        2 => 'DISABLED',
    ];
    // constants
    const ENABLE        = 1;

    const DISABLED      = 2;

    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getStatus(int $value){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : '';
    }
}