<?php
/**
 * 活动报名审核状态枚举
 */
namespace App\Enums;


class ActivityRegisterAuditEnum extends BaseEnum
{
    //审核状态
    public static $labels = [
        'PENDING_REVIEW'    => '待审核',
        'PASS'              => '已通过',
        'TURN_DOWN'         => '已驳回',
    ];

    public static $audit = [
        0 => 'PENDING_REVIEW',
        1 => 'PASS',
        2 => 'TURN_DOWN',
    ];

    // constants

    const PENDING_REVIEW    = 0;    //审核状态-待审核

    const PASS              = 1;    //审核状态-已通过

    const TURN_DOWN         = 2;    //审核状态-已驳回

    /**
     * 获取身份标签
     * @param int $value
     * @return mixed|string
     */
    public static function getAudit($value){
        return isset(self::$audit[$value]) ? self::$labels[self::$audit[$value]] : '';
    }
}