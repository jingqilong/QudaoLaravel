<?php


namespace App\Enums;


class ProcessEventMessageTypeEnum  extends BaseEnum
{
    public static $labels=[
        //状态
        'EXCUTE_NOTICE'         => '执行通知',
        'STATUS_NOTICE'         => '进度通知',
    ];

    public static $data_map = [
        1 => 'EXCUTE_NOTICE',
        2 => 'STATUS_NOTICE',
    ];
    // constants
    const EXCUTE_NOTICE        = 1;

    const STATUS_NOTICE        = 2;

    /**
     * 获取状态label
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getLabelByValue($value,$default = ''){
        return isset(self::$data_map[$value]) ? self::$labels[self::$data_map[$value]] : $default;
    }
}