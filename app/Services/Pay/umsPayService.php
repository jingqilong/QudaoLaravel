<?php


namespace App\Services\Pay;

use App\Library\UmsPay\Notify\JsonNotify;
use App\Library\UmsPay\UmsPay;
use Illuminate\Http\Response;

class UmsPayService extends BaseService
{

    private $umsPay;

    public function __construct()
    {
        $this->umsPay = new UmsPay();
    }


    /**
     * @param $request
     * @return mixed $response
     */
    public function createOrder($request){
        $order_no = $request['order_no'];
        $amount = $request['amount'];
        $response = $this->umsPay->createOrder($order_no,$amount);
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