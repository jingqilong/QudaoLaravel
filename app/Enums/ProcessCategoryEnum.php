<?php
/**
 * 流程分类枚举
 */
namespace App\Enums;


class ProcessCategoryEnum extends BaseEnum
{

    public static $labels=[
        //网关类型
        'ROUTE'         => '路由',
        'REPOSITORY'    => '仓库',
        'RESOURCE'      => '资源',

        //状态
        'INACTIVE'      => '未激活',
        'ACTIVE'        => '激活',
    ];

    public static $getway_type = [
        0 => 'ROUTE',
        1 => 'REPOSITORY',
        2 => 'RESOURCE',
    ];

    public static $status = [
        0 => 'INACTIVE',
        1 => 'ACTIVE',
    ];
    // constants

    const ROUTE             = 0;

    const REPOSITORY        = 1;

    const RESOURCE          = 2;

    const INACTIVE          = 0;

    const ACTIVE            = 1;

    /**
     * 获取网管类型label
     * @param int $value
     * @return mixed|string
     */
    public static function getGetWayType(int $value){
        return isset(self::$getway_type[$value]) ? self::$labels[self::$getway_type[$value]] : '';
    }
    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getStatus(int $value){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : '';
    }
}