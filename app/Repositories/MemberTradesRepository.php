<?php


namespace App\Repositories;

use App\Enums\TradeEnum;
use App\Models\MemberTradesModel;
use App\Repositories\Traits\RepositoryTrait;

class MemberTradesRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberTradesModel $model)
    {
        $this->model = $model;
    }

    /**
     * 添加交易记录
     * @param $add_arr
     * @param string $fund_flow
     * @param int $status
     * @return integer|null
     */
    protected function addTrade($add_arr, $fund_flow = '+', $status = TradeEnum::STATUSTRADING){
        $trade_add = [
            'trade_no'      => self::getTradeNo(),
            'order_id'      => $add_arr['order_id'],
            'pay_user_id'   => $add_arr['pay_user_id'],
            'payee_user_id' => $add_arr['payee_user_id'],
            'amount'        => $add_arr['amount'],
            'fund_flow'     => $fund_flow,
            'trade_method'  => $add_arr['pay_method'],
            'status'        => $status,
            'create_at'     => time()
        ];
        if ($status == TradeEnum::STATUSSUCCESS){
            $trade_add['end_at'] = time();
        }
        return MemberTradesRepository::getAddId($trade_add);
    }

    /**
     * 生成交易号
     * @param int $length
     * @return string
     */
    private function getTradeNo($length = 8)
    {
        $number = '0123456789';
        for ($i = 0, $num = '', $lc = strlen($number)-1; $i < $length; $i++) {
            $num .= $number[mt_rand(0, $lc)];
        }
        $time = time();
        $num  = $time.$num;
        if ($this->exists(['trade_no' => $num])){
            return self::getTradeNo($length);
        }
        return $num;
    }
}
            