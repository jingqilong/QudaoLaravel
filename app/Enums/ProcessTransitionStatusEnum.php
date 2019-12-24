<?php
/**
 * 流程节点流转状态枚举
 */
namespace App\Enums;


class ProcessTransitionStatusEnum extends BaseEnum
{

    public static $labels=[
        'GO_ON'         => '继续',
        'END'           => '结束',
        'TERMINATE'     => '终止'
    ];

    public static $data_map = [
        1 => 'GO_ON',
        2 => 'END',
        3 => 'TERMINATE',
    ];
    // constants
    const GO_ON         = 1;

    const END           = 2;

    const TERMINATE     = 3;

    /**
     *
     * @param int $value
     * @return mixed|string
     */
    public static function getLabelByValue(int $value){
        return isset(self::$data_map[$value]) ? self::$labels[self::$data_map[$value]] : '';
    }
}