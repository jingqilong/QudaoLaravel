<?php
/**
 * 支付方式枚举
 */
namespace App\Enums;


class PayMethodEnum extends BaseEnum
{
    //支付方式
    public static $labels = [
        1           => 'WECHATPAY',
        2           => 'UMSPAY',
        'H5'        => 'h5',
        'GZH'       => 'gzh',
        '94'        => '94',
        'MD5'       => 'MD5',
        'SHA256'    => 'SHA256',
        'SM3'       => 'SM3',
    ];

    //银联支付场景
    public static $scenario = [
        1 => 'H5',      //  h5 手机 浏览器使用
        2 => 'GZH',     //  微信支付宝云闪付 app 内的浏览器中进行支付
    ];


    //加密方式
    public static $encryption = [
        1 => 'MD5',
        2 => 'SHA256',
        3 => 'SM3',
    ];


    //云闪付
    public static $payway = [
        1 => '94',      //  银联在线(云闪付)
    ];





    // constants

    const WECHATPAY         = 1;    //微信支付

    const UMSPAY            = 2;    //银联支付

    const H5                = 1;    //支付场景  h5 手机 浏览器使用

    const GZH               = 2;    //支付场景  微信支付宝云闪付 app 内的浏览器中进行支付

    const MD5               = 1;    //加密方式

    const SHA256            = 2;    //加密方式

    const SM3               = 3;    //加密方式   默认方式

    const PAYWAY            = 1;    //云闪付

    /**
     * @param int $value        值
     * @param string $default   默认值
     * @return mixed|string
     */
    public static function getScenario(int $value,$default = ''){
        return isset(self::$scenario[$value]) ? self::$labels[self::$scenario[$value]] : $default;
    }

    /**
     * @param int $value        值
     * @param string $default   默认值
     * @return mixed|string
     */
    public static function getEncryption(int $value,$default = ''){
        return isset(self::$encryption[$value]) ? self::$labels[self::$encryption[$value]] : $default;
    }


    /**
     * @param int $value        值
     * @param string $default   默认值
     * @return mixed|string
     */
    public static function getPayway(int $value,$default = ''){
        return isset(self::$payway[$value]) ? self::$labels[self::$payway[$value]] : $default;
    }


}