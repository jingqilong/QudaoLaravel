<?php
/**
 * 成员等级申请枚举
 */
namespace App\Enums;


class GradeOrderEnum extends BaseEnum
{

    public static $labels=[
        //状态
        'PAYMENT'       => '待支付',
        'EVALUATION'    => '已支付',
        'CANCELED'      => '已取消',
        //审核状态
        'PENDING'       => '待审核',
        'PASS'          => '通过',
        'NOPASS'        => '驳回',
    ];

    //状态
    public static $status = [
        0 => 'PAYMENT',
        1 => 'EVALUATION',
        2 => 'CANCELED',
    ];

    //审核状态
    public static $audit_status = [
        0 => 'PENDING',
        1 => 'PASS',
        2 => 'NOPASS',
    ];

    // 状态
    const PAYMENT           = 0;    //待支付

    const EVALUATION        = 1;    //已支付

    const CANCELED          = 2;    //已取消

    //审核状态
    const PENDING           = 0;    //待审核

    const PASS              = 1;    //通过

    const NOPASS            = 2;    //驳回


    /**
     * 获取状态
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getStatus(int $value,$default = ''){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : $default;
    }

    /**
     * 获取是否可购买
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getAuditStatus(int $value,$default = ''){
        return isset(self::$audit_status[$value]) ? self::$labels[self::$audit_status[$value]] : $default;
    }

}