<?php
/**
 * 流程分类枚举
 */

namespace App\Enums;


class ProcessCategoryEnum extends BaseEnum
{
    public static $labels=[
        'MEMBER_REGISTER'        => '成员注册',
        'MEMBER_UPGRADE'         => '成员升级',
        'ACTIVITY_REGISTER'      => '活动报名',
        'PROJECT_DOCKING'        => '项目对接',
        'LOAN_RESERVATION'       => '贷款预约',
        'ENTERPRISE_CONSULT'     => '企业咨询',
    ];

    public static $data_map = [
        1 => 'MEMBER_REGISTER',
        2 => 'MEMBER_UPGRADE',
        3 => 'ACTIVITY_REGISTER',
        4 => 'PROJECT_DOCKING',
        5 => 'LOAN_RESERVATION',
        6 => 'ENTERPRISE_CONSULT',
    ];
    // constants

    const MEMBER_REGISTER          = 1;

    const MEMBER_UPGRADE           = 2;

    const ACTIVITY_REGISTER        = 3;

    const PROJECT_DOCKING          = 4;

    const LOAN_RESERVATION         = 5;

    const ENTERPRISE_CONSULT       = 6;

    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getLabelByValue(int $value){
        return isset(self::$data_map[$value]) ? self::$labels[self::$data_map[$value]] : '';
    }
}