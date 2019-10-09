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
        'INACTIVE'      => '非激活状态',
        'ACTIVE'        => '激活状态',
    ];
    // constants

    const ROUTE             = 0;

    const REPOSITORY        = 1;

    const RESOURCE          = 2;

    const INACTIVE          = 0;

    const ACTIVE            = 1;
}