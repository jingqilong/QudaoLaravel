<?php
namespace App\Library\UmsPay\Utils;

/**
 * Class UmsConstants
 * @package App\Library\UmsPay\Utils
 */
class UmsConstants
{
    /**
     * 支付环境定义： test，production
     */
    const PAY_ENV = 'test';
    /**
     * 支付返回的URL 中的HASH(路由部分)
     */
    const RETURN_URL_HASH = '/#/pages/shop/pay/payResultH5';
    /**
     * 支付URL 测试
     */
    const TEST_PAY_URL ="https://dhjt-test.chinaums.com/queryService/UmsWebPayPlugins";
    /**
     * 查询URL 测试
     */
    const TEST_QUERY_URL ="https://dhjt-test.chinaums.com/queryService/UmsWebPayQuery";
    /**
     * 退款URL 测试
     */
    const TEST_REFUND_URL="https://dhjt-test.chinaums.com/queryService/UmsWebPayRefund";
    /**
     * 文档中没有，微信群中说，这是返回的URL。（）
     */
    const TEST_RETURN_URL ="https://wx.test.qudaoplus.cn";
    /**
     * 文档中没有，微信群中说，这是返回的URL。（）
     */
    const TEST_NOTIFY_URL ="https://api.test.qudaoplus.cn/api/v1/payments/ums_pay_call_back";

    /**
     * 支付URL 生产
     */
    const PAY_URL ="https://dhjt.chinaums.com/queryService/UmsWebPayPlugins";
    /**
     * 查询URL 生产
     */
    const QUERY_URL ="https://dhjt.chinaums.com/queryService/UmsWebPayQuery";
    /**
     * 退款URL 生产
     */
    const REFUND_URL="https://dhjt.chinaums.com/queryService/UmsWebPayRefund";
    /**
     * 校验字符串
     */
    const CHECK_STR="1111111111111111111111111111111111111111111111111111111111111111";
    /**
     * 商户ID
     */
    const STATIC_MER_ID="f672e52f01b04676ad7339095aa60d5f";////联系大华捷通项目组获取
    /**
     * 文档中没有，微信群中说，这是返回的URL。（）
     */
    const RETURN_URL ="https://wx.qudaoplus.cn";
    /**
     * 文档中没有，微信群中说，这是返回的URL。（）
     */
    const NOTIFY_URL ="https://api.qudaoplus.cn/api/v1/payments/ums_pay_call_back";
    /// OP_狮子歌歌:
    //@祁宏 祁总，商户号898319973110223
    //
    //OP_狮子歌歌:
    //终端02230003
    /// transMid=898319973110223，transTid=02230003
    ///
}