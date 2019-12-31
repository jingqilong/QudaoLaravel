<?php
/**
 * 成员枚举
 */
namespace App\Enums;


class MemberIsTestEnum extends BaseEnum
{

    public static $labels=[
        'NO_TEST'           => '非测试人员',
        'TEST'              => '测试人员',
    ];

    public static $is_test = [
        0 => 'NO_TEST',
        1 => 'TEST',
    ];

    const NO_TEST           = 0;    //非测试人员
    const TEST              = 1;    //测试人员


    /**
     * 获取状态label
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getIsTest(int $value,$default = ''){
        return isset(self::$is_test[$value]) ? self::$labels[self::$is_test[$value]] : $default;
    }
}