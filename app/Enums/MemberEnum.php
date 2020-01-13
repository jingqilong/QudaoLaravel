<?php
/**
 * 成员枚举
 */
namespace App\Enums;


class MemberEnum extends BaseEnum
{

    public static $labels=[
        //成员级别 (本组废弃)
//        'DEFAULT'             => '普通成员',
//        'TEST'                => '内部测试',
//        'ALSOENJOY'           => '亦享成员',
//        'TOENJOY'             => '至享成员',
//        'YUEENJOY'            => '悦享成员',
//        'REALLYENJOY'         => '真享成员',
//        'YOUENJOY'            => '君享成员',
//        'HONOURENJOY'         => '尊享成员',
//        'ZHIRENJOY'           => '致享成员',
//        'ADVISER'             => '高级顾问',
//        'TEMPORARY'           => '临时成员',
        //成员分类
        'SHANGZHENG'          => '商政名流',
        'QIYEJINGYING'        => '企业精英',
        'HONOURMEMBER'        => '名医专家',
        'MINGYIZHUANJIA'      => '文艺雅仕',
        //成员性别
        'NOSET'               => '',
        'MAN'                 => '先生',
        'WOMAN'               => '女士',
        //成员身份
        'OFFICER'             => '官员',
        'MEMBER'              => '成员',
        //状态
        'HIDDEN'              => '隐藏',
        'ACTIVITE'            => '显示',
        //通过
        'NOPASS'              => '未审核',
        'PASS'                => '通过',
        //等级 身份
        'GRADE'               => '成员等级',
        'IDENTITY'            => '身份类型',
        //有效期
        'ONEYEAR'             => '一年',
        'TWOYEAR'             => '两年',
        'THREEYEAR'           => '三年',
        'FIVEYEAR'            => '五年',
        'PERMANENT'           => '永久有效',
        //审核状态
        'SUBMIT'              => '待审核',
        'EXAMINATIONPASS'     => '审核通过',
        'EXAMINATIONNOPASS'   => '审核驳回',
        'CANCEL'              => '预约取消',
    ];



    //成员等级 (本组废弃)
//    public static $grade = [
//        0  => 'DEFAULT',
//        1  => 'TEST',
//        2  => 'ALSOENJOY',
//        3  => 'TOENJOY',
//        4  => 'YUEENJOY',
//        5  => 'REALLYENJOY',
//        6  => 'YOUENJOY',
//        7  => 'HONOURENJOY',
//        8  => 'ZHIRENJOY',
//        9  => 'ADVISER',
//        10 => 'TEMPORARY',
//    ];

    //成员分类
    public static $category = [

        1 => 'SHANGZHENG',
        2 => 'QIYEJINGYING',
        3 => 'HONOURMEMBER',
        4 => 'MINGYIZHUANJIA',
    ];

    //等级 身份
    public static $identity = [
        1 => 'GRADE',
        2 => 'IDENTITY',
    ];

    //成员性别
    public static $sex = [
        0 => 'NOSET',
        1 => 'MAN',
        2 => 'WOMAN',
    ];

    //成员身份
    public static $status = [
        1 => 'OFFICER',
        0 => 'MEMBER',
    ];

    //状态
    public static $hidden = [
        0 => 'ACTIVITE',
        1 => 'HIDDEN',
    ];

    //排序
    public static $sort = [
        3 => 'RECOMMEND',
    ];

    //pass
    public static $pass = [
        0 => 'NOPASS',
        1 => 'PASS',
    ];

    //pass
    public static $expiration = [
        0 => 'PERMANENT',
        1 => 'ONEYEAR',
        2 => 'TWOYEAR',
        3 => 'THREEYEAR',
        4 => 'FIVEYEAR',
    ];

    //审核状态
    public static $audit_status = [
        0 => 'SUBMIT',            //待审核
        1 => 'EXAMINATIONPASS',   //审核通过
        2 => 'EXAMINATIONNOPASS', //审核驳回
        3 => 'CANCEL',            //取消预约
    ];

    // 成员等级  (本组废弃)
//    const DEFAULT          = 0;    //默认
//
//    const TEST             = 1;    //内部测试
//
//    const ALSOENJOY        = 2;    //亦享成员
//
//    const TOENJOY          = 3;    //至享成员
//
//    const YUEENJOY         = 4;    //悦享成员
//
//    const REALLYENJOY      = 5;    //真享成员
//
//    const YOUENJOY         = 6;    //君享成员
//
//    const HONOURENJOY      = 7;    //尊享成员
//
//    const ZHIRENJOY        = 8;    //致享成员
//
//    const ADVISER          = 9;    //高级顾问
//
//    const TEMPORARY        = 10;   //临时成员


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

    const MEMBER            = 0;    //成员

    //状态
    const ACTIVITE          = 1;    //显示

    const HIDDEN            = 2;    //隐藏

    //排序
    const RECOMMEND         = 3;    //推荐排序

    //通过
    const NOPASS            = 0;    //通过
    const PASS              = 1;    //未通过

    //通过
    const GRADE             = 1;    //成员等级
    const IDENTITY          = 2;    //身份类型

    //有效期
    const PERMANENT         = 0;    //永久有效
    const ONEYEAR           = 1;    //一年
    const TWOYEAR           = 2;    //两年
    const THREEYEAR         = 3;    //三年
    const FIVEYEAR          = 4;    //五年

    //贷款 审核状态

    const SUBMIT            = 0;    //待审核

    const EXAMINATIONPASS   = 1;    //审核通过

    const EXAMINATIONNOPASS = 2;    //审核驳回

    const CANCEL            = 3;    //取消预约


    /**
     * 获取状态label
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getStatus(int $value,$default = ''){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : $default;
    }

//    /**
//     * 获取状态label
//     * @param int $value
//     * @param string $default
//     * @return mixed|string
//     */
//    public static function getGrade($value,$default = ''){
//        if (empty($value)) return $default;
//        return isset(self::$grade[$value]) ? self::$labels[self::$grade[$value]] : $default;
//    }

    /**
     * 获取状态label
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getCategory($value,$default = ''){
        if (empty($value)){
            return $default;
        }
        return isset(self::$category[$value]) ? self::$labels[self::$category[$value]] : $default;
    }

    /**
     * 获取状态label
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getSex(int $value,$default = ''){
        return isset(self::$sex[$value]) ? self::$labels[self::$sex[$value]] : $default;
    }

    /**
     * 获取状态label
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getHidden(int $value,$default = ''){
        return isset(self::$hidden[$value]) ? self::$labels[self::$hidden[$value]] : $default;
    }
    /**
     * 获取状态label
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getExpiration(int $value,$default = ''){
        return isset(self::$expiration[$value]) ? self::$labels[self::$expiration[$value]] : $default;
    }
    /**
     * 获取状态label
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getIdentity(int $value,$default = ''){
        return isset(self::$identity[$value]) ? self::$labels[self::$identity[$value]] : $default;
    }
    /**
     * 获取状态label
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getAuditStatus(int $value,$default = ''){
        return isset(self::$audit_status[$value]) ? self::$labels[self::$audit_status[$value]] : $default;
    }

}