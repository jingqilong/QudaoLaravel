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
        //展示顺序
        'FIRST'     => '第一张',
        'SECOND'    => '第二张',
        'THIRD'     => '第三张',
        'FOUR'      => '第四张',
        //展示状态
        'SHOW'      => '展示',
        'HIDDEN'    => '隐藏'
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

    //展示顺序
    public static $sort = [
        1 => 'FIRST',
        2 => 'SECOND',
        3 => 'THIRD',
        4 => 'FOUR',
    ];

    //展示状态
    public static $status = [
        1 => 'SHOW',
        2 => 'HIDDEN',
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

    #展示顺序
    const FIRST         = 1;    //第一张

    const SECOND        = 2;    //第二张

    const THIRD         = 3;    //第三张

    const FOUR          = 4;    //第四张

    #展示状态
    const SHOW          = 1;    //展示

    const HIDDEN        = 2;    //隐藏

    /**
     * 获取banner类别label
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getBannerType($value, $default = ''){
        return isset(self::$banner_type[$value]) ? self::$labels[self::$banner_type[$value]] : $default;
    }

    /**
     * 获取banner类别label
     * @param int $value
     * @param string $default
     * @return mixed|string
     */
    public static function getBannerModule($value, $default = ''){
        return isset(self::$banner_module[$value]) ? self::$labels[self::$banner_module[$value]] : $default;
    }

    /**
     * 获取展示顺序
     * @param $value
     * @param string $default
     * @return mixed|string
     */
    public static function getSort($value, $default = ''){
        return isset(self::$sort[$value]) ? self::$labels[self::$sort[$value]] : $default;
    }
    /**
     * 获取展示状态
     * @param $value
     * @param string $default
     * @return mixed|string
     */
    public static function getStatus($value, $default = ''){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : $default;
    }
}