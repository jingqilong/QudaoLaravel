<?php
/**
 * 流程分类枚举
 */

namespace App\Enums;


class ProcessCategoryEnum extends BaseEnum
{
    public static $labels=[
        'MEMBER_UPGRADE'         => '成员升级',
        'ACTIVITY_REGISTER'      => '活动报名',
        'PROJECT_DOCKING'        => '项目对接',
        'LOAN_RESERVATION'       => '贷款预约',
        'ENTERPRISE_CONSULT'     => '企业咨询',
        'HOUSE_RESERVATION'      => '看房预约',
        'HOSPITAL_RESERVATION'   => '医疗预约',
        'PRIME_RESERVATION'      => '精选生活预约',
    ];

    public static $data_map = [
        1 => 'MEMBER_UPGRADE',
        2 => 'ACTIVITY_REGISTER',
        3 => 'PROJECT_DOCKING',
        4 => 'LOAN_RESERVATION',
        5 => 'ENTERPRISE_CONSULT',
        6 => 'HOUSE_RESERVATION',
        7 => 'HOSPITAL_RESERVATION',
        8 => 'PRIME_RESERVATION',
    ];
    // constants

    const MEMBER_UPGRADE           = 1;

    const ACTIVITY_REGISTER        = 2;

    const PROJECT_DOCKING          = 3;

    const LOAN_RESERVATION         = 4;

    const ENTERPRISE_CONSULT       = 5;

    const HOUSE_RESERVATION        = 6;

    const HOSPITAL_RESERVATION     = 7;

    const PRIME_RESERVATION        = 8;

    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getLabelByValue(int $value){
        return isset(self::$data_map[$value]) ? self::$labels[self::$data_map[$value]] : '';
    }
}