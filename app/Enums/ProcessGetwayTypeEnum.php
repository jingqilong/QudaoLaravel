<?php
/**
 * 流程网关类型枚举
 */
namespace App\Enums;


class ProcessGetwayTypeEnum extends BaseEnum
{

    public static $labels=[
        //网关类型
        'ROUTE'         => '路由',
        'REPOSITORY'    => '仓库',
        'RESOURCE'      => '资源',
    ];

    public static $getway_type = [
        0 => 'ROUTE',
        1 => 'REPOSITORY',
        2 => 'RESOURCE',
    ];

    // constants
    const ROUTE             = 0;

    const REPOSITORY        = 1;

    const RESOURCE          = 2;

    /**
     * 获取网管类型label
     * @param int $value
     * @return mixed|string
     */
    public static function getGetWayType(int $value){
        return isset(self::$getway_type[$value]) ? self::$labels[self::$getway_type[$value]] : '';
    }

}