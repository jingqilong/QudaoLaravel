<?php
/**
 * 消息枚举
 */
namespace App\Enums;


class MessageEnum extends BaseEnum
{
    public static $labels=[
        //消息类型状态
        'OPEN'          => '正常',
        'DISABLE'       => '禁用',

    ];

    public static $category = [
        0   => 'OPEN',
        1   => 'DISABLE'
    ];

    // constants

    const OPEN          = 1;

    const DISABLE       = 1;

    /**
     * @param int $value        值
     * @param string $default   默认值
     * @return mixed|string
     */
    public static function getCategoryStatus(int $value,$default = ''){
        return isset(self::$category[$value]) ? self::$labels[self::$category[$value]] : $default;
    }
}