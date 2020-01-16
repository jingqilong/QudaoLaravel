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
        'HOUSE_RELEASE'          => '房源发布',
        'MEMBER_CONTACT_REQUEST' => '成员联系请求',
        'SHOP_NEGOTIABLE_ORDER'  => '商城面议订单',
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
        9 => 'HOUSE_RELEASE',
        10 => 'MEMBER_CONTACT_REQUEST',
        11 => 'SHOP_NEGOTIABLE_ORDER',
    ];
    // constants

    const MEMBER_UPGRADE           = 1; //成员升级

    const ACTIVITY_REGISTER        = 2; //活动报名

    const PROJECT_DOCKING          = 3; //项目对接

    const LOAN_RESERVATION         = 4; //贷款预约

    const ENTERPRISE_CONSULT       = 5; //企业咨询

    const HOUSE_RESERVATION        = 6; //看房预约

    const HOSPITAL_RESERVATION     = 7; //医疗预约

    const PRIME_RESERVATION        = 8; //精选生活预约

    const HOUSE_RELEASE            = 9; //房源发布

    const MEMBER_CONTACT_REQUEST   = 10;    //成员联系请求

    const SHOP_NEGOTIABLE_ORDER    = 11;    //商城面议订单

    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getLabelByValue(int $value){
        return isset(self::$data_map[$value]) ? self::$labels[self::$data_map[$value]] : '';
    }

    /**
     * 获取数据校验字符串
     * @return string
     */
    public static function getCheckString(){
        return implode(',',array_keys(self::$data_map));
    }
}