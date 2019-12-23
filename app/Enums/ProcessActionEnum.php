<?php
/**
 * 流程动作枚举
 */
namespace App\Enums;


class ProcessActionEnum extends BaseEnum
{

    public static $labels=[
        'ACCEPT'        => '同意',
        'REJECT'        => '拒绝',
        'CANCEL'        => '取消',
        'RESTART'       => '重启',
    ];

    public static $data_map = [
        1 => 'ACCEPT',
        2 => 'REJECT',
        3 => 'CANCEL',
        4 => 'RESTART',
    ];
    // constants
    const ACCEPT        = 1;

    const REJECT        = 2;

    const CANCEL        = 3;

    const RESTART       = 4;

    /**
     *
     * @param int $value
     * @return mixed|string
     */
    public static function getLabelByValue(int $value){
        return isset(self::$data_map[$value]) ? self::$labels[self::$data_map[$value]] : '';
    }
}