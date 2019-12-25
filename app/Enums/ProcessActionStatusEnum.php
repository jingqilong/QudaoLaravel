<?php
/**
 * 流程动作状态枚举
 */
namespace App\Enums;

/**
 * Class ProcessActionStatusEnum
 * @package App\Enums
 * @deprecated true
 */
class ProcessActionStatusEnum extends BaseEnum
{

    /**
     * @var array
     * @deprecated true
     */
    public static $labels=[
        //状态
        'ENABLE'            => '启用',
        'DISABLE'           => '禁用',
     ];
    /**
     * @var array
     * @deprecated true
     */
    public static $data_map = [
        1 => 'ENABLE',
        2 => 'DISABLE',
    ];
    // constants
    const ENABLE        = 1;

    const DISABLE       = 2;

    /**
     * 获取状态label
     * @param int $value
     * @param string $defailt
     * @return mixed|string
     * @deprecated true
     */
    public static function getLabelByValue($value,$defailt = ''){
        return isset(self::$data_map[$value]) ? self::$labels[self::$data_map[$value]] : $defailt;
    }
}