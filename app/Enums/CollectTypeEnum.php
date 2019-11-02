<?php
/**
 * 收藏类型枚举
 */
namespace App\Enums;


class CollectTypeEnum extends BaseEnum
{
    //收藏类型
    public static $labels=[
        'ACTIVITY'      => '活动模块',
        'GOODS'         => '商品模块',
        'ESTATE'        => '房产模块',
        'PROJECT'       => '精选生活模块',
    ];

    // constants

    const ACTIVITY      = 1;

    const GOODS         = 2;

    const ESTATE        = 3;

    const PROJECT       = 4;
}