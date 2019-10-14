<?php
/**
 * 流程节点动作负责人枚举
 */
namespace App\Enums;


class ProcessPrincipalsEnum extends BaseEnum
{

    public static $labels=[
        //负责人身份
        'EXECUTOR'      => '执行人',
        'SUPERVISOR'    => '监督人',
        'AGENT'         => '代理人',
    ];

    public static $status = [
        1 => 'EXECUTOR',
        2 => 'SUPERVISOR',
        3 => 'AGENT',
    ];
    // constants
    const EXECUTOR          = 1;

    const SUPERVISOR        = 2;

    const AGENT             = 3;

    /**
     * 获取身份标签
     * @param int $value
     * @return mixed|string
     */
    public static function getStatus($value){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : '';
    }

    /**
     * 获取身份标识链接字符串
     * @return string
     */
    public static function getPrincipalString(){
        return implode(',',self::$status);
    }
}