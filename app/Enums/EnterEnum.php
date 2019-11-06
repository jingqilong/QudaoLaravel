<?php
/**
 * 贷款枚举
 */
namespace App\Enums;


class EnterEnum extends BaseEnum
{

    public static $labels=[
        //  审核状态
        'SUBMIT'              => '待审核',
        'PASS'                => '审核通过',
        'NOPASS'              => '审核驳回',
        'ENTERPRICE'          => '企业咨询',
        'DINING'              => '餐饮咨询',
        'COMPANY'             => '公司咨询',
        'BUSINESS'            => '商业咨询',
    ];

    public static $status = [
        0 => 'SUBMIT',      //待审核
        1 => 'PASS',        //审核通过
        2 => 'NOPASS',      //审核失败
    ];


    public static $type = [
        1 => 'ENTERPRICE',   //1企业咨询
        2 => 'DINING',       //2餐饮咨询
        3 => 'COMPANY',      //3公司咨询
        4 => 'BUSINESS',     //4商业咨询
    ];

    public static $sex = [
        1 => 'MAN',         //1男
        2 => 'WOMAN',       //2女
    ];

    //审核状态

    const PASS              = 1;    //审核通过

    const NOPASS            = 2;    //审核失败

    const SUBMIT            = 0;    //待审核

    //type  类型
    const ENTERPRICE          = 1;    //企业咨询

    const DINING              = 2;    //餐饮咨询

    const COMPANY             = 3;    //公司咨询

    const BUSINESS            = 4;    //商业咨询







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
}