<?php
namespace App\Library\UmsPay\Utils;

/**
 * Class UmsStatus
 * @package App\Library\UmsPay\Utils
 * @desc 银联返回状态解析类
 */
class UmsStatus
{
    const NEW_ORDER = 'NEW_ORDER';
    const UNKNOWN = 'UNKNOWN';
    const TRADE_CLOSED = 'TRADE_CLOSED';
    const WAIT_BUYER_PAY = 'WAIT_BUYER_PAY';
    const TRADE_SUCCESS = 'TRADE_SUCCESS';
    const TRADE_REFUND = 'TRADE_REFUND';

    public $status_label = [
         'NEW_ORDER' => '新订单',
         'UNKNOWN' => '不明确的交易状态',
         'TRADE_CLOSED' => '在指定时间段内未支付时关闭的交易；在交易完成全额退款成功时关闭的交易；支付失败的交易。TRADE_CLOSED 的交易不允许进行任何操作。',
         'WAIT_BUYER_PAY' => '交易创建，等待买家付款。',
         'TRADE_SUCCESS' => '支付成功',
         'TRADE_REFUND' => '订单转入退货流程'
    ];

}