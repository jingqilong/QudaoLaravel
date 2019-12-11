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
     * 支付URL 测试
     */
    const TEST_PAY_URL ="https://dhjt-test.chinaums.com/queryService/UmsWebPayPlugins?";
    /**
     * 查询URL 测试
     */
    const TEST_QUERY_URL ="https://dhjt-test.chinaums.com/queryService/UmsWebPayQuery?";
    /**
     * 退款URL 测试
     */
    const TEST_REFUND_URL="https://dhjt-test.chinaums.com/queryService/UmsWebPayRefund?";

    /**
     * 支付URL 生产
     */
    const PAY_URL ="https://dhjt.chinaums.com/queryService/UmsWebPayPlugins?";
    /**
     * 查询URL 生产
     */
    const QUERY_URL ="https://dhjt.chinaums.com/queryService/UmsWebPayQuery?";
    /**
     * 退款URL 生产
     */
    const REFUND_URL="https://dhjt.chinaums.com/queryService/UmsWebPayRefund?";
    /**
     * 校验字符串
     */
    const CHECK_STR="1111111111111111111111111111111111111111111111111111111111111111";
    /**
     * 商户ID
     */
    const STATIC_MER_ID="f672e52f01b04676ad7339095aa60d5f";////联系大华捷通项目组获取
    /// OP_狮子歌歌:
    //@祁宏 祁总，商户号898319973110223
    //
    //OP_狮子歌歌:
    //终端02230003
    /// transMid=898319973110223，transTid=02230003
    ///
}