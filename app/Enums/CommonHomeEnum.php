<?php
/**
 * 首页枚举
 */
namespace App\Enums;


class CommonHomeEnum extends BaseEnum
{

    public static $labels=[
        //banner类别
        'AD'        => '广告',
        'ACTIVITY'  => '精选活动',
        'MEMBER'    => '成员风采',
        'SHOP'      => '珍品商城',
        'HOUSE'     => '房产租售',
        'PRIME'     => '精选生活',
    ];

    //banner类别
    public static $banner_type = [
        1 => 'AD',
        2 => 'ACTIVITY',
        3 => 'MEMBER',
        4 => 'SHOP',
        5 => 'HOUSE',
        6 => 'PRIME',
    ];
    // banner类别
    const AD            = 1;    //广告

    const ACTIVITY      = 2;    //精选活动

    const MEMBER        = 3;    //成员风采

    const SHOP          = 0;    //珍品商城

    const HOUSE         = 1;    //房产租售

    const PRIME         = 1;    //精选生活

    /**
     * 获取banner类别label
     * @param int $value
     * @return mixed|string
     */
    public static function getBannerType(int $value){
        return isset(self::$banner_type[$value]) ? self::$labels[self::$banner_type[$value]] : '';
    }
}