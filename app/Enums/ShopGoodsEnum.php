<?php
/**
 * 商城商品枚举
 */
namespace App\Enums;


class ShopGoodsEnum extends BaseEnum
{

    public static $labels=[
        //商品状态
        'PUTAWAY'       => '上架',
        'UNSHELVE'      => '下架',
    ];

    public static $status = [
        1 => 'PUTAWAY',
        2 => 'UNSHELVE',
    ];
    // 商品状态
    const PUTAWAY           = 1;    //上架

    const UNSHELVE          = 2;    //下架

    /**
     * 获取状态label
     * @param int $value
     * @return mixed|string
     */
    public static function getStatus(int $value){
        return isset(self::$status[$value]) ? self::$labels[self::$status[$value]] : '';
    }
}