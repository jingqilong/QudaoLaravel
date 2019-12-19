<?php
/**
 * 首页枚举
 */
namespace App\Enums;


class CommonHomeEnum extends BaseEnum
{

    public static $labels=[
        #banner模块
        'MAINHOME'  => '主首页',
        'SHOPHOME'  => '商城首页',
        //banner类别
        'AD'        => '广告',
        'ACTIVITY'  => '精选活动',
        'MEMBER'    => '成员风采',
        'SHOP'      => '珍品商城',
        'HOUSE'     => '房产租售',
        'PRIME'     => '精选生活',
    ];
    #banner模块
    public static $banner_module = [
        1 => 'MAINHOME',
        2 => 'SHOPHOME',
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

    #banner模块
    const MAINHOME      = 1;    //主首页

    const SHOPHOME      = 2;    //商城首页


    // banner类别
    const AD            = 1;    //广告

    const ACTIVITY      = 2;    //精选活动

    const MEMBER        = 3;    //成员风采

    const SHOP          = 4;    //珍品商城

    const HOUSE         = 5;    //房产租售

    const PRIME         = 6;    //精选生活

    /**
     * 获取banner类别label
     * @param int $value
     * @return mixed|string
     */
    public static function getBannerType(int $value){
        return isset(self::$banner_type[$value]) ? self::$labels[self::$banner_type[$value]] : '';
    }
    /**
     * 获取banner类别label
     * @param int $value
     * @return mixed|string
     */
    public static function getBannerModule(int $value){
        return isset(self::$banner_module[$value]) ? self::$labels[self::$banner_module[$value]] : '';
    }
}