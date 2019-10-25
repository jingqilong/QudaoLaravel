<?php
namespace App\Library\UmsPay\Utils;

/**
 * Class UmsPayWay
 * @package App\Library\UmsPay\Utils
 */
class UmsPayWay
{
    /**
     * 微信 银联在线（云闪付）
     */
    public const PAY_WAY_WECHAT = '94';

    /**
     * 支付宝
     */
    public const PAY_WAY_ALIPAY = '98';

    /**
     * 银联二维码
     */
    public const PAY_WAY_QRCODE = '94';

    /**
     * chinapay  网关支付
     */
    public const PAY_WAY_CP = 'CP';

    /**
     * unionpay  网关支付
     */
    public const PAY_WAY_UP = 'UP';

    /**
     * unionpay
     */
    public const PAY_WAY_BB = 'BB';
}