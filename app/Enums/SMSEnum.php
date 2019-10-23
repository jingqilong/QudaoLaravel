<?php
/**
 * 七牛存储空间枚举
 */
namespace App\Enums;


class SMSEnum extends BaseEnum
{
    //菜单类型
    public static $labels=[
        'DEFAULT'          => '默认类型',
        'MEMBERLOGIN'      => '会员模块登录验证',
        'CHANGEPASSWORD'   => '短信修改密码',
        'REGISTER'         => '成员短信注册',
        'BINDMOBILE'       => '成员绑定手机号',
    ];

    //短信类型
    public static $type = [
        0 => 'DEFAULT',
        1 => 'MEMBERLOGIN',
        2 => 'CHANGEPASSWORD',
        3 => 'REGISTER',
        4 => 'BINDMOBILE',
    ];

    //短信类型所属的模块
    public static $module = [
        'member'    => [1,2,3,4],
        'common'    => [0],
    ];

    //注册类短信类型
    public static $register = [
        3 => '短信成员注册',
    ];

    //短信模板
    public static $sms_template = [
        0 => '【渠道PLUS】验证码：%s,有效时间为5分钟。如非本人操作，请忽略此短信。',
        1 => '【渠道PLUS】登录验证码：%s,有效时间为5分钟。如非本人操作，请忽略此短信。',
        2 => '【渠道PLUS】验证码：%s,有效时间为5分钟。如非本人操作，请忽略此短信。',
        3 => '【渠道PLUS】注册验证码：%s,有效时间为5分钟。如非本人操作，请忽略此短信。',
        4 => '【渠道PLUS】手机号绑定验证码：%s,有效时间为5分钟。如非本人操作，请忽略此短信。',
    ];

    // constants

    const DEFAULT           = 0;

    const MEMBERLOGIN       = 1;

    const CHANGEPASSWORD    = 2;

    const REGISTER          = 3;

    const BINDMOBILE        = 4;

    /**
     * 检查类型是否存在
     * @param $value
     * @return bool
     */
    public static function exists($value){
        return isset(self::$type[$value]);
    }

    /**
     * 检查类型是否为注册类型（注册类不需要手机号存在于数据库）
     * @param $value
     * @return bool
     */
    public static function isRegister($value){
        return isset(self::$register[$value]);
    }

    /**
     * 获取短信类型对应的模块
     * @param $type
     * @return bool|int|string
     */
    public static function getModule($type){
        foreach (self::$module as $name => $value){
            if (in_array($type, $value)){
                return $name;
            }
        }
        return false;
    }

    /**
     * 获取验证码模板
     * @param $type
     * @return mixed
     */
    public static function getTemplate($type){
        return self::$sms_template[$type];
    }
}