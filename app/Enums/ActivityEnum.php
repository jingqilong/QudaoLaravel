<?php
/**
 * 活动枚举
 */
namespace App\Enums;


class ActivityEnum extends BaseEnum
{

    public static $labels=[
        //用品来源
        'OWN'           => '自营',
        'THIRDPART'     => '第三方',
    ];

    public static $status = [
        1 => 'OWN',
        2 => 'THIRDPART',
    ];
    // 用品来源
    const OWN           = 1;    //自营

    const THIRDPART     = 2;    //第三方
    // 是否允许非会员参加
    const NOTALLOW      = 1;    //不允许

    const ALLOW         = 2;    //允许

    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getStatus(int $value){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : '';
    }
}