<?php
/**
 * 会员绑定表枚举
 */
namespace App\Enums;


class MemberBindEnum extends BaseEnum
{

    public static $labels=[
        //第三方登录类型
        'WECHAT'        => '微信',
    ];

    public static $type = [
        1 => 'WECHAT',
    ];
    // 第三方登录类型
    const WECHAT        = 1;    //微信

    /**
     * 获取三方绑定表类型label
     * @param int $value
     * @return mixed|string
     */
    public static function getType(int $value){
        return isset(self::$type[$value]) ? self::$labels[self::$type[$value]] : '';
    }
}