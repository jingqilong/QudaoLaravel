<?php


namespace App\Repositories;


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
     * @return integer|null
     */
    protected function addOrder($amount, $payment_amount , $user_id, $order_type){
        $add_arr = [
            'order_no'      => self::getOrderNo(),
            'user_id'       => $user_id,
            'order_type'    => $order_type,
            'amount'        => $amount,
            'payment_amount'=> $payment_amount,
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
    private function getOrderNo($length = 8){
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
}
            