<?php
namespace App\Services\Prime;


use App\Enums\MemberEnum;
use App\Enums\OrderEnum;
use App\Enums\PrimeTypeEnum;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\MemberRepository;
use App\Repositories\PrimeMerchantRepository;
use App\Repositories\PrimeReservationRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService as CommonImagesService;
use App\Services\Common\SmsService;
use App\Services\Member\OrdersService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\DB;
use Tolawho\Loggy\Facades\Loggy;

class ReservationService extends BaseService
{
    use HelpTrait;

    /**
     * 获取预约列表
     * @param $request
     * @param null $merchant_id
     * @return bool|mixed|null
     */
    public function reservationList($request, $merchant_id = null)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $state      = $request['state'] ?? null;
        $keywords   = $request['keywords'] ?? null;
        $order      = 'id';
        $desc_asc   = 'desc';
        $where      = ['id' => ['>',0]];
        $column     = ['id','merchant_id','order_no','name','mobile','time','memo','member_id','number','order_image_ids','state'];
        if (!empty($merchant_id)){
            $where['merchant_id'] = $merchant_id;
        }
        if (!empty($state)){
            $where['state'] = $state;
        }
        if (!empty($keywords)){
            $keywords = [$keywords => ['order_no','name','mobile','memo']];
            if (!$list = PrimeReservationRepository::search($keywords,$where,$column,$page,$page_num,$order,$desc_asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = PrimeReservationRepository::getList($where,$column,$order,$desc_asc,$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data'] = CommonImagesService::getListImages($list['data'], ['order_image_ids'=>'several']);
        $merchant_ids = array_column($list['data'],'merchant_id');
        $merchant_list= PrimeMerchantRepository::getList(['id' => ['in',$merchant_ids]],['id','name']);
        $order_nos = array_column($list['data'],'order_no');
        $order_list= MemberOrdersRepository::getList(['order_no' => ['in',$order_nos]],['order_no','amount','payment_amount','status']);
        foreach ($list['data'] as &$value){
            $value['merchant_name'] = '';
            if ($merchant = $this->searchArray($merchant_list,'id',$value['merchant_id'])){
                $value['merchant_name'] = reset($merchant)['name'];
            }
            $amount = 0;
            $payment_amount = 0;
            $status = -1;
            if ($order = $this->searchArray($order_list,'order_no',$value['order_no'])){
                $amount         = reset($order)['amount'];
                $payment_amount = reset($order)['payment_amount'];
                $status         = reset($order)['status'];
            }
            $value['time']              = date('Y年m月d日 H点i分',$value['time']);
            $value['state_title']       = PrimeTypeEnum::getReservationStatus($value['state']);
            $value['payment_status']    = OrderEnum::getStatus($status,'未付款');
            $value['amount']            = round($amount / 100,2).'元';
            $value['payment_amount']    = round($payment_amount / 100,2).'元';
            unset($value['merchant_id']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 预约
     * @param $request
     * @param $member_id
     * @return bool
     */
    public function reservation($request, $member_id)
    {
        if (!PrimeMerchantRepository::exists(['id' => $request['merchant_id'],'disabled' => 0])){
            $this->setError('商户信息不存在！');
            return false;
        }
        if (strtotime($request['time']) < time()){
            $this->setError('不能预约过去的时间！');
            return false;
        }
        $add_arr = [
            'merchant_id'   => $request['merchant_id'],
            'name'          => $request['name'],
            'mobile'        => $request['mobile'],
            'time'          => strtotime($request['time']),
            'memo'          => $request['memo'] ?? '',
            'member_id'     => $member_id,
            'number'        => $request['number'],
        ];
        if (PrimeReservationRepository::exists(array_merge(['state' => PrimeTypeEnum::RESERVATION],$add_arr))){
            $this->setError('已预约，请勿重复预约！');
            return false;
        }
        $add_arr['created_at']  = time();
        $add_arr['updated_at']  = time();
        if (PrimeReservationRepository::getAddId($add_arr)){
            $this->setMessage('预约成功！');
            return true;
        }
        $this->setError('预约失败！');
        return false;
    }

    /**
     * 预约审核
     * @param $id
     * @param $audit
     * @param null $merchant_id
     * @return bool
     */
    public function auditReservation($id, $audit,$merchant_id = null)
    {
        $where = ['id' => $id];
        if (!empty($merchant_id)){
            $where['merchant_id'] = $merchant_id;
        }
        if (!$reservation = PrimeReservationRepository::getOne($where)){
            $this->setError('预约不存在！');
            return false;
        }
        if ($reservation['state'] > PrimeTypeEnum::RESERVATION){
            $this->setError('预约已审核！');
            return false;
        }
        DB::beginTransaction();
        $order_no = null;
        if ($audit == 1){
            #如果审核通过，生成订单
            if (!$order_no = app(OrdersService::class)->placeOrder($reservation['member_id'],0,OrderEnum::PRIME)){
                Loggy::write('error','精选生活预约审核失败！失败原因：订单生成失败！预约ID：'.$id);
                DB::rollBack();
                $this->setError('审核失败！');
                return false;
            }
        }
        $status = ($audit == 1) ? PrimeTypeEnum::RESERVATIONOK : PrimeTypeEnum::RESERVATIONNO;
        if (!PrimeReservationRepository::getUpdId($where,['state' => $status,'order_no' => $order_no])){
            $this->setError('审核失败！');
            DB::rollBack();
            return false;
        }
        DB::commit();
        #通知用户
        if ($member = MemberRepository::getOne(['m_id' => $reservation['member_id']])){
            $member_name = $reservation['name'];
            $member_name = $member_name . MemberEnum::getSex($member['m_sex']);
            #短信通知
            if (!empty($member['m_phone'])){
                $smsService = new SmsService();
                $sms_template = [
                    PrimeTypeEnum::RESERVATIONOK => '尊敬的'.$member_name.'您好！您的精选生活预约已经通过审核，预约时间：'.date('Y年m月d日 H点i分',$reservation['time']).'！',
                    PrimeTypeEnum::RESERVATIONNO => '尊敬的'.$member_name.'您的精选生活预约未通过审核，再看看其他服务吧！',
                ];
                $smsService->sendContent($member['m_phone'],$sms_template[$status]);
            }
        }
        $this->setMessage('审核成功！');
        return true;
    }

    /**
     * 结算账单
     * @param $request
     * @param null $merchant_id
     * @return bool
     */
    public function billSettlement($request, $merchant_id = null)
    {
        $where = ['id' => $request['id']];
        if (!empty($merchant_id)){
            $where['merchant_id'] = $merchant_id;
        }
        if (!$reservation = PrimeReservationRepository::getOne($where)){
            $this->setError('预约不存在！');
            return false;
        }
        if ($reservation['state'] !== PrimeTypeEnum::RESERVATIONOK){
            $this->setError('该预约未成功，无法结算！');
            return false;
        }
        if (empty($reservation['order_no'])){
            $this->setError('该预约未生成订单，无法结算！');
            return false;
        }
        if (!$order = MemberOrdersRepository::getOne(['order_no' => $reservation['order_no']])){
            $this->setError('该预约订单未找到，无法结算！');
            return false;
        }
        if ($order['status'] == OrderEnum::STATUSSUCCESS){
            $this->setError('该预约订单已结算！');
            return false;
        }
        DB::beginTransaction();
        $order_upd = [
            'amount'            => $request['amount'] * 100,
            'payment_amount'    => $request['payment_amount'] * 100,
            'status'            => OrderEnum::STATUSSUCCESS,
            'updated_at'        => time()
        ];
        if (!MemberOrdersRepository::getUpdId(['order_no' => $reservation['order_no']],$order_upd)){
            DB::rollBack();
            $this->setError('结算失败！');
            return false;
        }
        $reservation_upd = [
            'order_image_ids'   => $request['image_ids'],
            'updated_at'        => time()
        ];
        if (!PrimeReservationRepository::getUpdId(['id' => $reservation['id']],$reservation_upd)){
            DB::rollBack();
            $this->setError('结算失败！');
            return false;
        }
        DB::commit();
        $this->setMessage('结算成功！');
        return true;
    }
}
            