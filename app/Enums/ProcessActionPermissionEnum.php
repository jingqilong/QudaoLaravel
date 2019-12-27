<?php
/**
 * 流程记录权限枚举
 */
namespace App\Enums;


class ProcessActionPermissionEnum extends BaseEnum
{

    public static $labels=[
        'NO_PERMISSION'         => '无权限操作',
        'PERMISSION'            => '有权限操作',
    ];

    public static $data_map = [
        1 => 'NO_PERMISSION',
        0 => 'PERMISSION',
    ];
    // constants
    const NO_PERMISSION         = 0;

    const PERMISSION            = 1;

    /**
     *
     * @param int $value
     * @return mixed|string
     */
    public static function getLabelByValue(int $value){
        return isset(self::$data_map[$value]) ? self::$labels[self::$data_map[$value]] : '';
    }
}