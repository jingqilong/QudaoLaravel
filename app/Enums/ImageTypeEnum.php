<?php
/**
 * 图片类型枚举
 */
namespace App\Enums;


class ImageTypeEnum extends BaseEnum
{
    //菜单类型
    public static $labels=[
        'ACTIVITY'      => '活动模块',
        'ACTIVITYTEMP'  => '更改过后的活动模块',
        'ESTATE'        => '房产模块',
        'GOODS'         => '商品模块',
        'MEMBER'        => '会员模块',
        'PROJECT'       => '精选生活模块',
    ];

    // constants

    const ACTIVITY      = 1;

    const ACTIVITYTEMP  = 1;

    const ESTATE        = 3;

    const GOODS         = 4;

    const MEMBER        = 5;

    const PROJECT       = 6;
}