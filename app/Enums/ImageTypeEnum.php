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
        'NORMAL'        => '?imageView2/1/q/72|imageslim',
        'SMALL'         => '?imageView2/1/w/375/q/72|imageslim',
    ];

    //状态
    public static $size = [
        1 => 'NORMAL',
        2 => 'SMALL',
    ];


    // constants

    const ACTIVITY      = 1;

    const ACTIVITYTEMP  = 1;

    const ESTATE        = 3;

    const GOODS         = 4;

    const MEMBER        = 5;

    const PROJECT       = 6;
    #图片尺寸
    const NORMAL        = 1;

    const SMALL         = 2;

    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getSize(int $value){
        return isset(self::$size[$value]) ? self::$labels[self::$size[$value]] : '';
    }
}