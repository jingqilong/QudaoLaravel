<?php
/**
 * 流程定义表枚举
 */
namespace App\Enums;


class ProcessDefinitionStatusEnum extends BaseEnum
{

    public static $labels=[
        //状态
        'INACTIVE'      => '未启用',
        'ACTIVE'        => '启用',
    ];

    public static $data_map = [
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
    public static function getLabelByValue(int $value){
        return isset(self::$data_map[$value]) ? self::$labels[self::$data_map[$value]] : '';
    }
}