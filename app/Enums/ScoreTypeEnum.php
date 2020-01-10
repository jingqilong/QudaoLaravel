<?php


namespace App\Enums;


class ScoreTypeEnum
{
    public static $labels=[
        'PRESTIGE'       => '尊享积分',
        'LADY'           => '消费积分',
        'GOLD_COIN '     => '金币积分',
    ];

    public static $status = [
        1 => 'GENTLEMEN',
        2 => 'LADY',
        3 => 'GOLD_COIN'
    ];

    #score
    const GENTLEMEN     = 1;    //尊享积分

    const LADY          = 2;    //消费积分

    const GOLD_COIN     = 3;    //金币积分
}