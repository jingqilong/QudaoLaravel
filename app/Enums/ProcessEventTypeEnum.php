<?php


namespace App\Enums;


class ProcessEventTypeEnum extends BaseEnum
{
    public static $labels=[
        //状态
        'NODE'        => '节点',
        'RESULT'      => '结查',

    ];

    public static $data_map = [
        0 => 'NODE',
        1 => 'RESULT',
    ];
    // constants
    const NODE        = 0;

    const RESULT      = 0;


    /**
     * 获取状态label
     * @param int $value
     * @param string $defailt
     * @return mixed|string
     */
    public static function getLabelByValue($value,$defailt = ''){
        return isset(self::$data_map[$value]) ? self::$labels[self::$data_map[$value]] : $defailt;
    }
}