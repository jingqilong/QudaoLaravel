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

        'IMAGE'         => '图片文件',
        'VIDEO'         => '视频文件',
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

    //图片类别
    public static $file_type = [
        1 => 'IMAGE',
        2 => 'VIDEO',
    ];


    // banner类别
    const COMMON        = 0;    //公共

    const ACTIVITY      = 1;    //精彩活动

    const MEDICAL       = 2;    //医疗特约

    const ENTERPRISE    = 3;    //企业咨询

    const ESTATE        = 4;    //房产租售

    const AVATAR        = 5;    //会员头像

    const PROJECT       = 6;    //项目对接

    const MEMBER        = 7;    //成员风采

    const SPECIALS      = 8;    //精选生活

    const SHOP          = 9;    //商城模块

    const SPACE         = 10;    //私享空间



    const IMAGE         = 1;    //图片文件

    const VIDEO         = 2;    //视频文件

    /**
     * 获取图片类别标签
     * @param $type
     * @return mixed|string
     */
    public static function getImageType($type)
    {
        return isset(self::$image_type[$type]) ? self::$labels[self::$image_type[$type]] : '';
    }

    /**
     * 获取文件类别
     * @param $type
     * @return mixed|string
     */
    public static function getFileType($type)
    {
        return isset(self::$file_type[$type]) ? self::$labels[self::$file_type[$type]] : '';
    }

}