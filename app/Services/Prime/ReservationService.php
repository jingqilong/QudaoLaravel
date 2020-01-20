<?php
namespace App\Services\Prime;


use App\Enums\MemberEnum;
use App\Enums\MessageEnum;
use App\Enums\OrderEnum;
use App\Enums\PrimeTypeEnum;
use App\Enums\ProcessCategoryEnum;
use App\Repositories\MemberOrdersRepository;
use App\Repositories\MemberBaseRepository;
use App\Repositories\PrimeMerchantRepository;
use App\Repositories\PrimeReservationRepository;
use App\Repositories\PrimeReservationViewRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService as CommonImagesService;
use App\Services\Common\SmsService;
use App\Services\Member\OrdersService;
use App\Services\Message\SendService;
use App\Traits\BusinessTrait;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tolawho\Loggy\Facades\Loggy;

class ReservationService extends BaseService
{
    use HelpTrait,BusinessTrait;

    /**
     * 获取预约列表
     * @param $request
     * @return bool|mixed|null
     */
    public function reservationList($request)
    {
        $employee = Auth::guard('oa_api')->user();
        $state      = $request['state'] ?? null;
        $keywords   = $request['keywords'] ?? null;
        $merchant_id= $request['merchant_id'] ?? null;
        $order      = 'id';
        $desc_asc   = 'desc';
        $where      = ['id' => ['>',0]];
        $column     = ['id','merchant_id','order_no','name','mobile','time','memo','member_id','number','order_image_ids','state'];
        if (!empty($merchant_id)){
            $where['merchant_id'] = $merchant_id;
        }
        if (!is_null($state)){
            $where['state'] = $state;
        }
        if (!empty($keywords)){
            $keywords = [$keywords => ['order_no','name','mobile','memo']];
            if (!$list = PrimeReservationRepository::search($keywords,$where,$column,$order,$desc_asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = PrimeReservationRepository::getList($where,$column,$order,$desc_asc)){
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
        $merchant_list= PrimeMerchantRepository::getAllList(['id' => ['in',$merchant_ids]],['id','name']);
        $order_nos = array_column($list['data'],'order_no');
        $order_list= MemberOrdersRepository::getAllList(['order_no' => ['in',$order_nos]],['order_no','amount','payment_amount','status']);
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
            #获取流程信息
            $value['progress'] = $this->getBusinessProgress($value['id'],ProcessCategoryEnum::PRIME_RESERVATION,is_null($merchant_id) ? $employee->id : 0);
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
        DB::beginTransaction();
        if (!$id = PrimeReservationRepository::getAddId($add_arr)){
            $this->setError('预约失败！');
            DB::rollBack();
            return false;
        }
        #开启流程
        $start_process_result = $this->addNewProcessRecord($id,ProcessCategoryEnum::PRIME_RESERVATION);
        if (100 == $start_process_result['code']){
            $this->setError('预约失败，请稍后重试！');
            DB::rollBack();
            return false;
        }
        $this->setMessage('预约成功！');
        DB::commit();
        return true;
    }

    /**
     * 获取预约详情
     * @param $id
     * @param null $merchant_id
     * @return array|bool
     */
    public function reservationDetails($id,$merchant_id = null){
        $employee = Auth::guard('oa_api')->user();
        $column = ['id','merchant_id','time','longitude','latitude','number','state','merchant_name','type','banner_ids','star','address','name','mobile','memo','discount'];
        if (!$reservation = PrimeReservationViewRepository::getOne(['id' => $id],$column)){
            $this->setError('预约信息不存在！');
            return false;
        }
        $reservation        = CommonImagesService::getOneImagesConcise($reservation, ['banner_ids'=>'single']);
        $reservation['time'] = date('Y.m.d / H:i',$reservation['time']);
        $reservation['state_title']     = PrimeTypeEnum::getReservationStatus($reservation['state']);
        $reservation['type_title']      = PrimeTypeEnum::getType($reservation['type']);
        return $this->getBusinessDetailsProcess($reservation,ProcessCategoryEnum::PRIME_RESERVATION,is_null($merchant_id) ? $employee->id : 0);
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
        if ($member = MemberBaseRepository::getOne(['id' => $reservation['member_id']])){
            $member_name = $reservation['name'];
            $member_name = $member_name . MemberEnum::getSex($member['sex']);
            $sms_template = [
                PrimeTypeEnum::RESERVATIONOK => MessageEnum::getTemplate
                (
                    MessageEnum::PRIMEBOOKING,
                    'auditPass',
                    ['member_name' => $member_name,'time' => date('Y-m-d H:i',$reservation['time'])]
                ),
                PrimeTypeEnum::RESERVATIONNO => MessageEnum::getTemplate
                (
                    MessageEnum::PRIMEBOOKING,
                    'auditNoPass',
                    ['member_name' => $member_name,'time' => date('Y-m-d H:i',$reservation['time'])]
                ),
            ];
            #短信通知
            if (!empty($member['mobile'])){
                $smsService = new SmsService();
                $smsService->sendContent($member['mobile'],$sms_template[$status]);
            }
            $title = '房产预约通知';
            #发送站内信
            SendService::sendMessage($reservation['member_id'],MessageEnum::PRIMEBOOKING,$title,$sms_template[$status],$id);
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

    /**
     * 我的预约列表
     * @param $request
     * @return bool|mixed|null
     */
    public function myReservationList($request)
    {
        $member     = Auth::guard('member_api')->user();
        $type       = $request['type'] ?? null;
        $order      = 'id';
        $desc_asc   = 'desc';
        $where      = ['id' => ['>',0],'member_id' => $member->id];
        $column     = ['id','time','number','state','merchant_name','banner_ids','star','address'];
        if (!is_null($type)){
            $where['type'] = $type;
        }
        if (!$list = PrimeReservationViewRepository::getList($where,$column,$order,$desc_asc)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data'] = CommonImagesService::getListImagesConcise($list['data'], ['banner_ids'=>'single']);
        foreach ($list['data'] as &$value){
            $value['time']              = date('Y.m.d / H:i',$value['time']);
            $value['state_title']       = PrimeTypeEnum::getReservationStatus($value['state']);
            unset($value['banner_ids']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 获取我的预约详情
     * @param $id
     * @return mixed
     */
    public function myReservationDetail($id)
    {
        $column = ['id','merchant_id','time','longitude','latitude','number','state','merchant_name','type','banner_ids','star','address','name','mobile','memo','discount'];
        if (!$reservation = PrimeReservationViewRepository::getOne(['id' => $id],$column)){
            $this->setError('预约信息不存在！');
            return false;
        }
        $reservation        = CommonImagesService::getOneImagesConcise($reservation, ['banner_ids'=>'single']);
        $reservation['time'] = date('Y.m.d / H:i',$reservation['time']);
        $reservation['state_title']     = PrimeTypeEnum::getReservationStatus($reservation['state']);
        $reservation['type_title']      = PrimeTypeEnum::getType($reservation['type']);
        $this->setMessage('获取成功！');
        return $reservation;
    }

    /**
     * 修改我的预约
     * @param $request
     * @return bool
     */
    public function editMyReservation($request)
    {
        if (!$reservation = PrimeReservationRepository::getOne(['id' => $request['id']])){
            $this->setError('预约信息不存在！');
            return false;
        }
        if ($reservation['state'] !== PrimeTypeEnum::RESERVATION){
            $this->setError('您的已预约已受理，不能再修改了！');
            return false;
        }
        $upd_arr = [
            'name'          => $request['name'],
            'mobile'        => $request['mobile'],
            'time'          => strtotime($request['time']),
            'memo'          => $request['memo'] ?? '',
            'number'        => $request['number'],
            'updated_at'    => time(),
        ];
        if (!PrimeReservationRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('修改失败！');
            return false;
        }
        $this->setMessage('修改成功！');
        return true;
    }

    /**
     * 取消预约
     * @param $id
     * @return bool
     */
    public function cancelMyReservation($id)
    {
        $member = Auth::guard('member_api')->user();
        if (!$reservation = PrimeReservationRepository::getOne(['id' => $id,'member_id' => $member->id])){
            $this->setError('预约信息不存在！');
            return false;
        }
        if ($reservation['state'] == PrimeTypeEnum::RESERVATIONCANCEL){
            $this->setError('您的已预约已取消！');
            return false;
        }
        if ($reservation['state'] !== PrimeTypeEnum::RESERVATION){
            $this->setError('您的已预约已受理，无法取消！');
            return false;
        }
        $upd_arr = [
            'state'         => PrimeTypeEnum::RESERVATIONCANCEL,
            'updated_at'    => time(),
        ];
        DB::beginTransaction();
        if (!PrimeReservationRepository::getUpdId(['id' => $id],$upd_arr)){
            $this->setError('取消失败！');
            DB::rollBack();
            return false;
        }
        if (!$this->cancelBusinessProcess($id,ProcessCategoryEnum::PRIME_RESERVATION)){
            $this->setError('取消失败！');
            DB::rollBack();
            return false;
        }
        DB::commit();
        $this->setMessage('取消成功！');
        return true;
    }

    /**
     * 获取预约统计数据（OA后台首页展示）
     * @return array
     */
    public static function getStatistics(){
        $total_count    = PrimeReservationRepository::count(['id' => ['<>',0]]) ?? 0;
        $audit_count    = PrimeReservationRepository::count(['state' => ['in',[PrimeTypeEnum::RESERVATIONOK,PrimeTypeEnum::RESERVATIONNO]]]) ?? 0;
        $no_audit_count = PrimeReservationRepository::count(['state' => PrimeTypeEnum::RESERVATION]) ?? 0;
        $cancel_count   = PrimeReservationRepository::count(['state' => PrimeTypeEnum::RESERVATIONCANCEL]) ?? 0;
        return [
            'total'     => $total_count,
            'audit'     => $audit_count,
            'no_audit'  => $no_audit_count,
            'cancel'    => $cancel_count
        ];
    }

    /**
     * 获取申请人ID
     * @param $reservation_id
     * @return mixed
     */
    public function getCreatedUser($reservation_id){
        return PrimeReservationRepository::getField(['id' => $reservation_id],'member_id');
    }

    /**
     * 返回流程中的业务列表
     * @param $repository_ids
     * @return mixed
     */
    public function getProcessBusinessList($repository_ids){
        if (empty($repository_ids)){
            return [];
        }
        $column     = ['id','member_id','name','mobile'];
        if (!$order_list = PrimeReservationRepository::getAssignList($repository_ids,$column)){
            return [];
        }
        $result_list = [];
        foreach ($order_list as $value){
            $result_list[] = [
                'id'            => $value['id'],
                'name'          => '精选生活预约',
                'member_id'     => $value['member_id'],
                'member_name'   => $value['name'],
                'member_mobile' => $value['mobile'],
            ];
        }
        return $result_list;
    }
}
            