<?php
/**
 * 贷款枚举
 */
namespace App\Enums;


class DoctorEnum extends BaseEnum
{

    public static $labels=[
        //  审核状态
        'SUBMIT'              => '待审核',
        'PASS'                => '审核通过',
        'NOPASS'              => '审核驳回',
        'SEEDOCTOR'           => '看病',
        'SURGERY'             => '手术',
        'INHOSPATIL'          => '住院',
        'MAN'                 => '男',
        'WOMANTIL'            => '女',
        'PUBLICS'             => '公立',
        'PRIVATES'            => '私立',
        'SYNTHESIS'           => '综合',
    ];

    public static $status = [
        0 => 'SUBMIT',      //待审核
        1 => 'PASS',        //审核通过
        2 => 'NOPASS',      //审核失败
    ];


    public static $type = [
        1 => 'SEEDOCTOR',   //1看病
        2 => 'SURGERY',     //2手术
        3 => 'INHOSPATIL',  //3住院
    ];

    public static $sex = [
        1 => 'MAN',         //1男
        2 => 'WOMAN',       //2女
    ];

    public static $category = [
        1 => 'PUBLICS',         //1公立
        2 => 'PRIVATES',        //2私立
        3 => 'SYNTHESIS',       //3综合
    ];

    //审核状态

    const PASS              = 1;    //审核通过

    const NOPASS            = 2;    //审核失败

    const SUBMIT            = 0;    //待审核

    //type  类型
    const SEEDOCTOR              = 1;    //看病

    const SURGERY                = 2;    //手术

    const INHOSPATIL             = 3;    //住院



     //category  类型
    const PUBLICS                    = 1;    //公立

    const PRIVATES                   = 2;    //私立

    const SYNTHESIS                  = 3;    //综合

    //sex  类型
    const MAN                    = 1;    //男

    const WOMAN                  = 2;    //女







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
    public static function getType(int $value){
        return isset(self::$type[$value]) ? self::$labels[self::$type[$value]] : '';
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
    public static function getCategory(int $value){
        return isset(self::$category[$value]) ? self::$labels[self::$category[$value]] : '';
    }
}