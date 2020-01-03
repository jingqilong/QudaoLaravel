<?php
/**
 * 用户调研获知渠道枚举
 */
namespace App\Enums;


class UserSurveyHearFromEnum extends BaseEnum
{

    public static $labels=[
        'MAGAZINE'                  => '杂志',
        'WE_CHAT_PUBLIC_ACCOUNT'    => '微信公众号',
        'ACTIVITY_SITE'             => '活动现场',
        'FRIEND_INTRODUCTION'       => '朋友介绍',
        'OTHERS'                    => '其他'
    ];

    public static $status = [
        1 => 'MAGAZINE',
        2 => 'WE_CHAT_PUBLIC_ACCOUNT',
        3 => 'ACTIVITY_SITE',
        4 => 'FRIEND_INTRODUCTION',
        5 => 'OTHERS',
    ];

    const MAGAZINE                  = 1;    //杂志

    const WE_CHAT_PUBLIC_ACCOUNT    = 2;    //微信公众号

    const ACTIVITY_SITE             = 3;    //活动现场

    const FRIEND_INTRODUCTION       = 4;    //朋友介绍

    const OTHERS                    = 5;    //其他

//    /**
//     * 获取展示状态
//     * @param $value
//     * @param string $default
//     * @return mixed|string
//     */
//    public static function getLabel($value, $default = ''){
//        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : $default;
//    }


    /**
     * 获取检查有效性的字符串
     * @return string
     */
    public static function getCheckString(){
        $keys = array_keys(self::$status);
        return implode($keys,',');
    }
}