<?php
/**
 * 流程事件枚举
 */
namespace App\Enums;


class ProcessEventEnum extends BaseEnum
{

    public static $labels=[
        //状态
        'DINGTALK_EMAIL'        => '启用',
        'SMS'                   => '禁用',
        'SITE_MESSAGE'          => '启用',
        'WECHAT_PUSH'           => '禁用',
     ];

    public static $data_map = [
        1 => 'DINGTALK_EMAIL',
        2 => 'SMS',
        3 => 'SITE_MESSAGE',
        4 => 'WECHAT_PUSH',
    ];
    // constants
    const DINGTALK_EMAIL        = 1;

    const SMS                   = 2;

    const SITE_MESSAGE          = 3;

    const WECHAT_PUSH           = 4;

    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getLabelByValue(int $value){
        return isset(self::$data_map[$value]) ? self::$labels[self::$data_map[$value]] : '';
    }
}