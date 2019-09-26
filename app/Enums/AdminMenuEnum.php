<?php
/**
 * OA系统菜单枚举
 */
namespace App\Enums;


class AdminMenuEnum extends BaseEnum
{
    //交易状态
    public static $labels = [
        0 => 'DIRECTORY',
        1 => 'MENU',
        2 => 'OPERATE',
    ];

    // constants

    const DIRECTORY         = 0;    //目录

    const MENU              = 1;    //菜单

    const OPERATE           = 2;    //操作

}