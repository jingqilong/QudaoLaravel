<?php
return [
    /**
     * 支付环境定义： test，production
     */
     'PAY_ENV'          =>  'test',
    /**
     * 支付URL 测试
     */
     'TEST_PAY_URL'     =>  "https://dhjt-test.chinaums.com/queryService/UmsWebPayPlugins?",
    /**
     * 查询URL 测试
     */
     'TEST_QUERY_URL'   =>  "https://dhjt-test.chinaums.com/queryService/UmsWebPayQuery?",
    /**
     * 退款URL 测试
     */
     'TEST_REFUND_URL'  =>  "https://dhjt-test.chinaums.com/queryService/UmsWebPayRefund?",

    /**
     * 支付URL 生产
     */
     'PAY_URL'          =>  "https://dhjt.chinaums.com/queryService/UmsWebPayPlugins?",
    /**
     * 查询URL 生产
     */
     'QUERY_URL'        =>  "https://dhjt.chinaums.com/queryService/UmsWebPayQuery?",
    /**
     * 退款URL 生产
     */
    'REFUND_URL'        =>  "https://dhjt.chinaums.com/queryService/UmsWebPayRefund?",
    /**
     * 校验字符串
     */
    'CHECK_STR'         =>  "1111111111111111111111111111111111111111111111111111111111111111",
    /**
     * 商户ID
     */
     'STATIC_MER_ID'    =>  "a2e1173cf833483fb69a19ad4df33652" ////联系大华捷通项目组获取
];