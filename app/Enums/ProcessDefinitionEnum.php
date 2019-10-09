<?php
/**
 * 流程定义表枚举
 */
namespace App\Enums;


class ProcessDefinitionEnum extends BaseEnum
{

    public static $labels=[
        //状态
        'INACTIVE'      => '未激活',
        'ACTIVE'        => '激活',
    ];

    public static $status = [
        0 => 'INACTIVE',
        1 => 'ACTIVE',
    ];
    // constants
    const INACTIVE          = 0;

    const ACTIVE            = 1;

    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getStatus(int $value){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : '';
    }
}