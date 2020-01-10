<?php


namespace App\Enums;


class ShopInventorChangeEnum
{

    public static $labels = [
        'PURCHASE'           => '采购入库',
        'SELLING'            => '销售出库',
        'ADJUSTMENT'         => '库存调整',
    ];

    public static $status = [
        1 => 'PURCHASE',
        2 => 'SELLING',
        3 => 'ADJUSTMENT',
    ];

    const PURCHASE             = 1;    //订单状态-待支付

    const SELLING              = 2;    //订单状态-待发货

    const ADJUSTMENT           = 3;    //订单状态-已发货

    public static function getLabels(int $value,$default = ''){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : $default;
    }
}