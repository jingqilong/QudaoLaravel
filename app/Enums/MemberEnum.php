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
        //成员分类
        'SHANGZHENG'          => '商政名流',
        'QIYEJINGYING'        => '企业精英',
        'HONOURMEMBER'        => '名医专家',
        'MINGYIZHUANJIA'      => '文艺雅仕',
        //成员或官员状态
        'ACTIVITEMEMBER'      => '激活成员',
        'DISABLEMEMBER'       => '禁用成员',
        'ACTIVITEOFFICER'     => '激活官员',
        'DISABLEOFFICER'      => '禁用官员',
        'DELETEMEMBER'        => '删除成员',
    ];



    //成员等级
    public static $grade = [
        1 => 'TEST',
        2 => 'ALSOENJOY',
        3 => 'TOENJOY',
        4 => 'YUEENJOY',
        5 => 'REALLYENJOY',
        6 => 'YOUENJOY',
        7 => 'HONOURENJOY',
        8 => 'ZHIRENJOY',
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
        9 => 'DELETEMEMBER',
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


    //成员分类
    const SHANGZHENG        = 1;    //商政名流

    const QIYEJINGYING      = 2;    //企业精英

    const HONOURMEMBER      = 3;    //名医专家

    const MINGYIZHUANJIA    = 4;    //文艺雅仕


    //成员分类
    const ACTIVITEMEMBER    = 0;    //激活成员

    const DISABLEMEMBER     = 1;    //禁用成员

    const ACTIVITEOFFICER   = 2;    //激活官员

    const DISABLEOFFICER    = 3;    //禁用官员

    const DELETEMEMBER      = 9;    //删除成员


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
        return isset(self::$grade[$value]) ? self::$labels[self::$status[$value]] : '';
    }
    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getCategory(int $value){
        return isset(self::$category[$value]) ? self::$labels[self::$status[$value]] : '';
    }

}