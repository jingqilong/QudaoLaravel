<?php


namespace App\Repositories;


use App\Enums\OrderEnum;
use App\Models\MemberOrdersModel;
use App\Repositories\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Cache;

class MemberOrdersRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberOrdersModel $model)
    {
        $this->model = $model;
    }

    /**
     * 添加订单
     * @param $amount
     * @param $payment_amount
     * @param $user_id
     * @param $order_type
     * @param int $score_deduction
     * @param int $score_type
     * @return integer|null
     */
    protected function addOrder($amount, $payment_amount , $user_id, $order_type,$score_deduction = 0,$score_type = 0){
        $add_arr = [
            'order_no'      => self::getOrderNo(),
            'user_id'       => $user_id,
            'order_type'    => $order_type,
            'amount'        => $amount,
            'payment_amount'=> $payment_amount,
            'score_deduction'=> $score_deduction,
            'score_type'    => $score_type,
            'status'        => 0,
            'create_at'    => time()
        ];
        //此处做缓存为防止重复提交，10秒钟内只能提交一次
        Cache::put(md5($user_id.$order_type.$amount),$add_arr,5);
        return $this->getAddId($add_arr);
    }

    /**
     * 生成订单号
     * @param int $length
     * @return string
     */
    protected function getOrderNo($length = 8){
        $number = '0123456789';
        for ($i = 0, $num = '', $lc = strlen($number)-1; $i < $length; $i++) {
            $num .= $number[mt_rand(0, $lc)];
        }
        $time = date('Ymd');
        $num  = $time.$num;
        if ($this->exists(['order_no' => $num])){
            return self::getOrderNo($length);
        }
        return $num;
    }

    protected function addNegotiableGoodsOrder($member_id){
        $order_add_arr = [
            'order_no'          => MemberOrdersRepository::getOrderNo(),
            'user_id'           => $member_id,
            'order_type'        => OrderEnum::SHOP,
            'amount'            => 0,
            'payment_amount'    => 0,
            'score_deduction'   => 0,
            'score_type'        => 0,
            'status'            => OrderEnum::STATUSTRADING,
            'create_at'         => time(),
            'updated_at'        => time(),
        ];
        if (!$order_id = $this->getAddId($order_add_arr)){
            return false;
        }
        $order_add_arr['id'] = $order_id;
        return $order_add_arr;
    }
}
            