<?php
/**
 * 流程记录状态枚举
 */
namespace App\Enums;


class ProcessRecordStatusEnum extends BaseEnum
{

    public static $labels=[
        'DEFAULT'       => '正常',
        'STOPPED'       => '已停止',
    ];

    public static $status = [
        0 => 'DEFAULT',
        1 => 'STOPPED',
    ];
    // constants
    const DEFAULT           = 0;

    const STOPPED           = 1;

    /**
     *
     * @param int $value
     * @return mixed|string
     */
    public static function getLabelByValue(int $value){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : '';
    }
}