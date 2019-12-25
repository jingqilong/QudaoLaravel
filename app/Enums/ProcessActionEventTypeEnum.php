<?php
/**
 * 流程节点动作结果事件类型枚举
 */
namespace App\Enums;

/**
 * Class ProcessActionStatusEnum
 * @package App\Enums
 */
class ProcessActionEventTypeEnum extends BaseEnum
{

    /**
     * @var array
     */
    public static $labels=[
        'NODE_EVENT'            => '节点事件',
        'ACTION_RESULT_EVENT'   => '动作结果事件',
     ];
    /**
     * @var array
     */
    public static $data_map = [
        0 => 'NODE_EVENT',
        1 => 'ACTION_RESULT_EVENT',
    ];
    // constants
    const NODE_EVENT            = 0;

    const ACTION_RESULT_EVENT   = 1;

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