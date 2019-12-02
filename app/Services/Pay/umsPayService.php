<?php


namespace App\Services\Pay;

use App\Enums\OrderEnum;
use App\Enums\PayMethodEnum;
use App\Library\UmsPay\Notify\JsonNotify;
use App\Library\UmsPay\UmsPay;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\MemberTradesLogRepository;
use App\Repositories\MemberTradesRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UmsPayService extends BaseService
{

    private $umsPay;

    public function __construct()
    {
        $this->umsPay = new UmsPay();
    }


    /**
     * 银联下单
     * @param $request
     * @return mixed $response
     */
    public function createOrder($request){
        $user = Auth::guard('member_api')->user();
        $order_no = $request['order_no'];
        $amount = $request['amount'];
        if (!$order = MemberOrdersRepository::getOne(['order_no' => $order_no])){
            $this->setError('订单信息不存在！');
            return false;
        }
        switch ($order['status']){
            case OrderEnum::STATUSSUCCESS:
                $this->setError('订单已完成交易！');
                return false;
                break;
            case OrderEnum::STATUSCLOSE:
                $this->setError('订单关闭，无法进行交易！');
                return false;
                break;
        }
        DB::beginTransaction();
        //创建交易信息
        if ($order['trade_id'] == 0){#生成交易信息
            $trade_add = [
                'order_id'      => $order['id'],
                'pay_user_id'   => $user->id,
                'payee_user_id' => 0,
                'amount'        => $amount * 100,
                'pay_method'    => PayMethodEnum::UMSPAY
            ];
            if (!$trade_id = MemberTradesRepository::addTrade($trade_add)){
                DB::rollBack();
                $this->setError('生成交易信息失败！');
                return false;
            }
            #添加交易日志
            MemberTradesLogRepository::addLog($trade_id,$trade_add['amount'],'添加交易记录',
                '用户：【'.$user->m_phone.'】于'.date('Y-m-d H:m:s').'添加了交易记录，交易金额：'.$trade_add['amount'].', 交易状态：待付款！');
            if (!MemberOrdersRepository::getUpdId(['id' => $order['id']], ['trade_id' => $trade_id])){
                DB::rollBack();
                $this->setError('生成交易信息失败！');
                return false;
            }
        }
        $response = $this->umsPay->createOrder($order_no,$amount);
        DB::commit();
        $this->setMessage('下单成功！');
        return $response;
    }

    /**
     * @param $request
     * @return array
     */
    public function queryClearDate($request){
        $order_no = $request['order_no'];
        $clear_date = $request['clear_date'];
        $response = $this->umsPay->queryClearDate($order_no,$clear_date);
        return $response;
    }

    /**
     * @param $request
     * @return array
     */
    public function queryTransDate($request){
        $order_no = $request['order_no'];
        $trans_date = $request['trans_date'];
        $response = $this->umsPay->queryTransDate($order_no,$trans_date);
        return $response;
    }

    /**
     * @param $request
     * @return array
     */
    public function queryBySystemCode($request){
        $order_no = $request['order_no'];
        $response = $this->umsPay->queryBySystemCode($order_no);
        return $response;
    }

    /**
     * @param $request
     * @return array
     */
    public function refund($request){
        $order_no = $request['order_no'];
        $response = $this->umsPay->refund($order_no);
        return $response;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function payCallBack($request){
        $jsonNotify = new JsonNotify();
        $response = $jsonNotify->doPost($request);
        return $response;
    }
}