<?php
/**
 * 成员等级订单付款状态枚举
 */
namespace App\Enums;


class MemberGradeOrderStatusEnum extends BaseEnum
{

    public static $labels=[
        //付款状态
        'PAYMENT'           => '待付款',
        'EVALUATION'        => '已付款',
        'CANCEL'            => '已取消',
    ];

    //状态
    public static $status = [
        0 => 'PAYMENT',
        1 => 'EVALUATION',
        2 => 'CANCEL',
    ];

    // 状态
    const PAYMENT               = 0;    //待付款

    const EVALUATION            = 1;    //已付款

    const CANCEL                = 2;    //已取消


    /**
     * 获取状态
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getStatus(int $value,$default = ''){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : $default;
    }
}