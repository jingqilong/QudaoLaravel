<?php
/**
 * 公共审核状态枚举
 */
namespace App\Enums;


class CommonAuditStatusEnum extends BaseEnum
{

    public static $labels=[
        'SUBMIT'            => '待审核',
        'PASS'              => '已通过',
        'NO_PASS'           => '已驳回'
    ];

    public static $status = [
        0 => 'SUBMIT',
        1 => 'PASS',
        2 => 'NO_PASS',
    ];

    #banner模块
    const SUBMIT        = 0;    //待审核

    const PASS          = 1;    //已通过

    const NO_PASS       = 2;    //已驳回

}