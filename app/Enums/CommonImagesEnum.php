<?php
/**
 * 公共图片枚举
 */
namespace App\Enums;


class CommonImagesEnum extends BaseEnum
{

    public static $labels=[
        //图片类别
        'COMMON'        => '公共',
        'ACTIVITY'      => '精彩活动',
        'MEDICAL'       => '医疗特约',
        'ENTERPRISE'    => '企业咨询',
        'ESTATE'        => '房产租售',
        'AVATAR'        => '会员头像',
        'PROJECT'       => '项目对接',
        'MEMBER'        => '成员风采',
        'SPECIALS'      => '精选生活',
        'SHOP'          => '商城模块',
        'SPACE'         => '私享空间',
    ];

    //图片类别
    public static $image_type = [
        0 => 'COMMON',
        1 => 'ACTIVITY',
        2 => 'MEDICAL',
        3 => 'ENTERPRISE',
        4 => 'ESTATE',
        5 => 'AVATAR',
        6 => 'PROJECT',
        7 => 'MEMBER',
        8 => 'SPECIALS',
        9 => 'SHOP',
        10=> 'SPACE',
    ];
    // banner类别
    const COMMON        = 1;    //公共

    const ACTIVITY      = 2;    //精彩活动

    const MEDICAL       = 3;    //医疗特约

    const ENTERPRISE    = 4;    //企业咨询

    const ESTATE        = 5;    //房产租售

    const AVATAR        = 6;    //会员头像

    const PROJECT       = 7;    //项目对接

    const MEMBER        = 8;    //成员风采

    const SPECIALS      = 9;    //精选生活

    const SHOP          = 10;   //商城模块

    /**
     * 获取图片类别标签
     * @param $type
     * @return mixed|string
     */
    public static function getImageType($type)
    {
        return isset(self::$image_type[$type]) ? self::$labels[self::$image_type[$type]] : '';
    }

}