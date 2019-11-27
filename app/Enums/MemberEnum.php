<?php
/**
 * 成员枚举
 */
namespace App\Enums;


class MemberEnum extends BaseEnum
{

    public static $labels=[
        //成员级别
        'TEST'                => '内部测试',
        'ALSOENJOY'           => '亦享成员',
        'TOENJOY'             => '至享成员',
        'YUEENJOY'            => '悦享成员',
        'REALLYENJOY'         => '真享成员',
        'YOUENJOY'            => '君享成员',
        'HONOURENJOY'         => '尊享成员',
        'ZHIRENJOY'           => '致享成员',
        'ADVISER'             => '高级顾问',
        'TEMPORARY'           => '临时成员',
        //成员分类
        'SHANGZHENG'          => '商政名流',
        'QIYEJINGYING'        => '企业精英',
        'HONOURMEMBER'        => '名医专家',
        'MINGYIZHUANJIA'      => '文艺雅仕',
        //成员或官员状态
        'ACTIVITEMEMBER'      => '成员激活中',
        'DISABLEMEMBER'       => '成员禁用中',
        'ACTIVITEOFFICER'     => '官员激活中',
        'DISABLEOFFICER'      => '官员禁用中',
        //成员性别
        'NOSET'               => '未设置',
        'MAN'                 => '先生',
        'WOMAN'               => '女士',
        //成员身份
        'OFFICER'             => '官员',
        'MEMBER'              => '成员',
    ];



    //成员等级
    public static $grade = [
        1  => 'TEST',
        2  => 'ALSOENJOY',
        3  => 'TOENJOY',
        4  => 'YUEENJOY',
        5  => 'REALLYENJOY',
        6  => 'YOUENJOY',
        7  => 'HONOURENJOY',
        8  => 'ZHIRENJOY',
        9  => 'ADVISER',
        10 => 'TEMPORARY',
    ];

    //成员分类
    public static $category = [

        1 => 'SHANGZHENG',
        2 => 'QIYEJINGYING',
        3 => 'HONOURMEMBER',
        4 => 'MINGYIZHUANJIA',
    ];

    //成员or官员or软删除   状态
    public static $status = [

        0 => 'ACTIVITEMEMBER',
        1 => 'DISABLEMEMBER',
        2 => 'ACTIVITEOFFICER',
        3 => 'DISABLEOFFICER',
    ];

    //成员性别
    public static $sex = [

        0 => 'NOSET',
        1 => 'MAN',
        2 => 'WOMAN',
    ];

    //成员身份
    public static $identity = [

        1 => 'OFFICER',
        2 => 'MEMBER',
    ];

    // 成员等级
    const TEST             = 1;    //内部测试

    const ALSOENJOY        = 2;    //亦享成员

    const TOENJOY          = 3;    //至享成员

    const YUEENJOY         = 4;    //悦享成员

    const REALLYENJOY      = 5;    //真享成员

    const YOUENJOY         = 6;    //君享成员

    const HONOURENJOY      = 7;    //尊享成员

    const ZHIRENJOY        = 8;    //致享成员

    const ADVISER          = 9;    //高级顾问

    const TEMPORARY        = 10;   //临时成员


    //成员分类
    const SHANGZHENG        = 1;    //商政名流

    const QIYEJINGYING      = 2;    //企业精英

    const HONOURMEMBER      = 3;    //名医专家

    const MINGYIZHUANJIA    = 4;    //文艺雅仕


    //成员状态
    const ACTIVITEMEMBER    = 0;    //激活成员

    const DISABLEMEMBER     = 1;    //禁用成员

    const ACTIVITEOFFICER   = 2;    //激活官员

    const DISABLEOFFICER    = 3;    //禁用官员


    //成员性别
    const NOSET             = 0;    //未设置

    const MAN               = 1;    //先生

    const WOMAN             = 2;    //女士

    //成员身份
    const OFFICER           = 1;    //官员

    const MEMBER            = 2;    //成员


    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getStatus(int $value){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : '';
    }

    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getGrade(int $value){
        return isset(self::$grade[$value]) ? self::$labels[self::$grade[$value]] : '';
    }
    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getCategory(int $value){
        return isset(self::$category[$value]) ? self::$labels[self::$category[$value]] : '';
    }

    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getSex(int $value){
        return isset(self::$sex[$value]) ? self::$labels[self::$sex[$value]] : '';
    }
    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getIdentity(int $value){
        return isset(self::$identity[$value]) ? self::$labels[self::$identity[$value]] : '';
    }

}