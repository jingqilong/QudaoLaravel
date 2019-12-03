<?php
/**
 * 七牛存储空间枚举
 */
namespace App\Enums;


class QiNiuEnum extends BaseEnum
{
    //菜单类型
    public static $labels=[
        'ACTIVITY'      => '精彩活动',
        'DOCTOR'        => '医疗特约',
        'ENTERPRISE'    => '企业咨询',
        'ESTATE'        => '房产-租赁',
        'HEADING'       => '会员头像',
        'ITEMS'         => '项目对接',
        'MEMBER'        => '成员风采',
        'PROJECT'       => '精选生活',
        'SHOP'          => '商城模块',
        'SPACE'         => '私享空间',
        'ACTIVITYVIDEO' => '活动视频',
    ];

    public static $spaces = [
        1 => 'qudao-activity-img',
        2 => 'qudao-doctor-img',
        3 => 'qudao-enterprise-img',
        4 => 'qudao-estate-img',
        5 => 'qudao-heading ',
        6 => 'qudao-items-img',
        7 => 'qudao-member-img',
        8 => 'qudao-project-img',
        9 => 'qudao-shop-img',
        10 => 'qudao-space-img',
        11 => 'video-qudao',
    ];
    public static $module = [
        1 => 'ACTIVITY',
        2 => 'DOCTOR',
        3 => 'ENTERPRISE',
        4 => 'ESTATE',
        5 => 'HEADING',
        6 => 'ITEMS',
        7 => 'MEMBER',
        8 => 'PROJECT',
        9 => 'SHOP',
        10 => 'SPACE',
        11 => 'ACTIVITYVIDEO',
    ];

    // constants

    const ACTIVITY      = 1;

    const DOCTOR        = 2;

    const ENTERPRISE    = 3;

    const ESTATE        = 4;

    const HEADING       = 5;

    const ITEMS         = 6;

    const MEMBER        = 7;

    const PROJECT       = 8;

    const SHOP          = 9;

    const SPACE         = 10;

    const ACTIVITYVIDEO = 11;

    /**
     * @param $const
     * @return mixed
     */
    public static function getSpace($const){
        return self::$spaces[$const];
    }

    public static function exists($value){
        return isset(self::$spaces[$value]);
    }
}