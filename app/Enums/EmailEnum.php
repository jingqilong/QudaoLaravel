<?php
/**
 * 邮件验证码枚举
 */
namespace App\Enums;


class EmailEnum extends BaseEnum
{
    //菜单类型
    public static $labels=[
        'DEFAULT'          => '默认类型',
        'LOGIN'            => '登录验证',
        'CHANGE_PASSWORD'  => '短信修改密码',
        'REGISTER'         => '邮箱注册',
        'BIND_EMAIL'       => '绑定邮箱',
    ];

    //邮件类型
    public static $type = [
        0 => 'DEFAULT',
        1 => 'MEMBERLOGIN',
        2 => 'CHANGEPASSWORD',
        3 => 'REGISTER',
        4 => 'BINDMOBILE',
    ];

    //邮件模板
    public static $template = [
        0 => '验证码：%s,有效时间为%s分钟。如非本人操作，请忽略此邮件。',
        1 => '登录验证码：%s,有效时间为%s分钟。如非本人操作，请忽略此邮件。',
        2 => '验证码：%s,有效时间为%s分钟。如非本人操作，请忽略此邮件。',
        3 => '注册验证码：%s,有效时间为%s分钟。如非本人操作，请忽略此邮件。',
        4 => '邮箱绑定验证码：%s,有效时间为%s分钟。如非本人操作，请忽略此邮件。',
    ];
    //邮件标题
    public static $title = [
        0 => '验证码：%s',
        1 => '登录验证码：%s',
        2 => '验证码：%s',
        3 => '注册验证码：%s',
        4 => '邮箱绑定验证码：%s',
    ];

    // constants

    const DEFAULT           = 0;    //默认类型

    const LOGIN             = 1;    //登录验证

    const CHANGE_PASSWORD   = 2;    //短信修改密码

    const REGISTER          = 3;    //邮箱注册

    const BIND_EMAIL        = 4;    //绑定邮箱

    /**
     * 检查类型是否存在
     * @param $value
     * @return bool
     */
    public static function exists($value){
        return isset(self::$type[$value]);
    }

    /**
     * 获取验证码模板
     * @param $type
     * @return mixed
     */
    public static function getTemplate($type){
        return self::$template[$type];
    }

    /**
     * 获取验证码标题
     * @param $type
     * @return mixed
     */
    public static function getTitle($type){
        return self::$title[$type];
    }
}