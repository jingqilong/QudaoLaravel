<?php
namespace App\Services\House;


use App\Enums\HouseEnum;
use App\Enums\MemberEnum;
use App\Enums\MessageEnum;
use App\Repositories\CommonImagesRepository;
use App\Repositories\HouseDetailsRepository;
use App\Repositories\HouseReservationRepository;
use App\Repositories\MemberBaseRepository;
use App\Repositories\MemberRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class ReservationService extends BaseService
{
    use HelpTrait;

    /**
     * 添加预约
     * @param $request
     * @param $member_id
     * @return bool
     */
    public function reservation($request, $member_id)
    {
        if (!HouseDetailsRepository::exists(['id' => $request['house_id'],'status' => HouseEnum::PASS,'deleted_at' => 0])){
            $this->setError('房产信息不存在！');
            return false;
        }
        if (strtotime($request['time']) < time()){
            $this->setError('不能预约过去的时间！');
            return false;
        }
        $add_arr = [
            'house_id'      => $request['house_id'],
            'name'          => $request['name'],
            'mobile'        => $request['mobile'],
            'time'          => strtotime($request['time']),
            'memo'          => $request['memo'] ?? '',
            'member_id'     => $member_id,
        ];
        if (HouseReservationRepository::exists(array_merge(['state' => HouseEnum::RESERVATION],$add_arr))){
            $this->setError('已预约，请勿重复预约！');
            return false;
        }
        $add_arr['created_at']  = time();
        $add_arr['updated_at']  = time();
        if (HouseReservationRepository::getAddId($add_arr)){
            $this->setMessage('预约成功！');
            return true;
        }
        $this->setError('预约失败！');
        return false;
    }

    /**
     * 获取预约列表
     * @param $request
     * @param int $member_id
     * @return bool|mixed|null
     */
    public function reservationList($request, $member_id = 0)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $state      = $request['state'] ?? null;
        $keywords   = $request['keywords'] ?? null;
        $order      = 'id';
        $desc_asc   = 'desc';
        $where = ['id' => ['<>',0]];
        if (!empty($member_id)){
            $where['member_id'] = $member_id;
        }
        if (!empty($state)){
            $where['state'] = $state;
        }
        $column = ['id','house_id','name','mobile','time','memo','state'];
        if (!empty($keywords)){
            $keywords = [$keywords => ['name','mobile','memo']];
            if (!$list = HouseReservationRepository::search($keywords,$where,$column,$page,$page_num,$order,$desc_asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = HouseReservationRepository::getList($where,$column,$order,$desc_asc,$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $house_ids = array_column($list['data'],'house_id');
        $house_list = HouseDetailsRepository::getList(['id' => ['in',$house_ids]],['id','title','category','area','condo_name','decoration','image_ids','area_code','address','rent','tenancy']);
        $house_list =  ImagesService::getListImagesConcise($house_list,['image_ids' => 'single']);
        foreach ($list['data'] as &$value){
            $value['condo_name']   = '';
            $value['decoration']   = '';
            $value['area']         = '';
            $value['category']     = '';
            $value['image_url']    = '';
            $value['house_title']  = '';
            $value['area_address'] = '';
            $value['rent'] = '';
            if ($house = $this->searchArray($house_list,'id',$value['house_id'])){
                $house = reset($house);
                $value['condo_name']            = $house['condo_name'];
                $value['decoration']            = HouseEnum::getDecoration($house['decoration'],'');
                $value['area']                  = $house['area'];
                $value['category']              = HouseEnum::getCategory($house['category']);
                $value['image_url']             = $house['image_url'];
                $value['house_title']           = $house['title'];
                list($area_address)   = $this->makeAddress($house['area_code'],$house['address']);
                $value['area_address']          = $area_address;
                $value['rent']                  = $house['rent'] .'元/'. HouseEnum::getTenancy($house['tenancy']);
            }
            $value['state_title']= HouseEnum::getReservationStatus($value['state']);
            $value['time']  = date('Y-m-d H:i:s',$value['time']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 审核预约
     * @param $id
     * @param $audit
     * @return bool
     */
    public function auditReservation($id, $audit)
    {
        if (!$reservation = HouseReservationRepository::getOne(['id' => $id])){
            $this->setError('预约不存在！');
            return false;
        }
        if ($reservation['state'] > HouseEnum::PENDING){
            $this->setError('预约已审核！');
            return false;
        }
        $status = ($audit == 1) ? HouseEnum::PASS : HouseEnum::NOPASS;
        if (!HouseReservationRepository::getUpdId(['id' => $id],['state' => $status])){
            $this->setError('审核失败！');
            return false;
        }
        #通知用户
        if ($member = MemberBaseRepository::getOne(['id' => $reservation['member_id']])){
            $member_name = $reservation['name'];
            $member_name = $member_name . MemberEnum::getSex($member['sex']);
            $sms_template = [
                HouseEnum::PASS         =>
                    MessageEnum::getTemplate(
                        MessageEnum::HOUSEBOOKING,
                        'auditPass',
                        ['member_name' => $member_name,'time' => date('Y-m-d H:i',$reservation['start_time'])]
                    ),
                HouseEnum::NOPASS       =>
                    MessageEnum::getTemplate(
                        MessageEnum::HOUSEBOOKING,
                        'auditNoPass',
                        ['member_name' => $member_name]
                    ),
            ];
            #短信通知
            if (!empty($member['mobile'])){
                $smsService = new SmsService();
                $smsService->sendContent($member['mobile'],$sms_template[$status]);
            }
            $title = '房产预约通知';
            #发送站内信
            SendService::sendMessage($reservation['member_id'],MessageEnum::HOUSEBOOKING,$title,$sms_template[$status],$id);
        }
        $this->setMessage('审核成功！');
        return true;
    }

    /**
     * 获取被预约列表
     * @param $request
     * @return mixed
     */
    public function isReservationList($request)
    {
        $member     = Auth::guard('member_api')->user();
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        if (!$house_list = HouseDetailsRepository::getList(['publisher' => HouseEnum::PERSON,'publisher_id' => $member->id])){
            $this->setMessage('您还没有发布过房源!');
            return [];
        }
        $house_ids = array_column($house_list,'id');
        if (!$reservation_list = HouseReservationRepository::getList(['house_id' => ['in',$house_ids],'state' => HouseEnum::RESERVATIONOK],['id','time','name','house_id'],'id','desc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $reservation_list = $this->removePagingField($reservation_list);
        if (empty($reservation_list['data'])){
            $this->setMessage('暂无预约！');
            return $reservation_list;
        }
        foreach ($reservation_list['data'] as &$value){
            $value['img_id'] = '';
            $value['house_title'] = '';
            if ($house = $this->searchArray($house_list,'id',$value['house_id'])){
                $value['img_id'] = reset($house)['image_ids'];
                $value['house_title'] = reset($house)['title'];
            }
            $value['time']  = date('Y-m-d H:i:s',$value['time']);
        }
        $reservation_list['data'] = ImagesService::getListImagesConcise($reservation_list['data'],['img_id' => 'single']);
        foreach ($reservation_list['data'] as &$value) unset($value['img_id']);
        $this->setMessage('获取成功！');
        return $reservation_list;
    }


    /**
     * 获取预约统计数据（OA后台首页展示）
     * @return array
     */
    public static function getStatistics(){
        $total_count    = HouseReservationRepository::count(['id' => ['<>',0]]) ?? 0;
        $audit_count    = HouseReservationRepository::count(['state' => ['in',[HouseEnum::PASS,HouseEnum::NOPASS]]]) ?? 0;
        $no_audit_count = HouseReservationRepository::count(['state' => HouseEnum::PENDING]) ?? 0;
        $cancel_count   = 0;
        return [
            'total'     => $total_count,
            'audit'     => $audit_count,
            'no_audit'  => $no_audit_count,
            'cancel'    => $cancel_count
        ];
    }

    /**
     * 前端获取我的预约详情
     * @param $id
     * @return bool|null
     */
    public function getReservationDetail($id)
    {
        $member = Auth::guard('member_api')->user();
        $column = ['id','house_id','name','mobile','time','memo','state'];
        if (!$reservation = HouseReservationRepository::getOne(['id' => $id,'member_id' => $member->id],$column)){
            $this->setError('预约不存在！');
            return false;
        }
        $reservation['state_title'] = HouseEnum::getReservationStatus($reservation['state']);
        $reservation['time']        = date('Y.m.d / H:i');
        $house_column = ['id','title','category','area','condo_name','decoration','image_ids','area_code','address','rent','tenancy','longitude','latitude'];
        if (!$house = HouseDetailsRepository::getOne(['id' => $reservation['house_id']],$house_column)){
            $this->setError('预约房产已下架！');
            return false;
        }
        $house = ImagesService::getOneImagesConcise($house,['image_ids' => 'single']);
        $reservation['condo_name']            = $house['condo_name'];
        $reservation['decoration']            = HouseEnum::getDecoration($house['decoration'],'');
        $reservation['area']                  = $house['area'];
        $reservation['category']              = HouseEnum::getCategory($house['category']);
        $reservation['image_url']             = $house['image_url'];
        $reservation['house_title']           = $house['title'];
        list($area_address)   = $this->makeAddress($house['area_code'],$house['address']);
        $reservation['area_address']          = $area_address;
        $reservation['longitude']             = $house['longitude'];
        $reservation['latitude']              = $house['latitude'];
        $reservation['rent']                  = $house['rent'] .'元/'. HouseEnum::getTenancy($house['tenancy']);
        $this->setMessage('获取成功！');
        return $reservation;
    }

    /**
     * 取消预约
     * @param $id
     * @return bool
     */
    public function cancelReservation($id)
    {
        $member = Auth::guard('member_api')->user();
        if (!$reservation = HouseReservationRepository::getOne(['id' => $id,'member_id' => $member->id])){
            $this->setError('预约不存在！');
            return false;
        }
        if ($reservation['state'] == HouseEnum::CANCELRESERVATION){
            $this->setError('预约已取消！');
            return false;
        }
        if (!HouseReservationRepository::getUpdId(['id' => $id],['state' => HouseEnum::CANCELRESERVATION,'updated_at' => time()])){
            $this->setError('预约取消失败！');
            return false;
        }
        $this->setMessage('预约取消成功！');
        return true;
    }

    /**
     * 修改预约
     * @param $request
     * @return bool
     */
    public function editReservation($request)
    {
        $member = Auth::guard('member_api')->user();
        if (!$reservation = HouseReservationRepository::getOne(['id' => $request['id'],'member_id' => $member->id])){
            $this->setError('预约不存在！');
            return false;
        }
        if ($reservation['state'] !== HouseEnum::RESERVATION){
            $this->setError('只有正在预约状态才能修改！');
            return false;
        }
        $request['time'] = str_replace('/','',$request['time']);
        $upd_arr = [
            'name'          => $request['name'],
            'mobile'        => $request['mobile'],
            'time'          => strtotime($request['time']),
            'memo'          => $request['memo'] ?? '',
            'updated_at'    => time()
        ];
        if (HouseReservationRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取申请人ID
     * @param $reservation_id
     * @return mixed
     */
    public function getCreatedUser($reservation_id){
        return HouseReservationRepository::getField(['id',$reservation_id],'member_id');
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
        if (!$order_list = HouseReservationRepository::getAssignList($repository_ids,$column)){
            return [];
        }
        $result_list = [];
        foreach ($order_list as $value){
            $result_list[] = [
                'id'            => $value['id'],
                'name'          => '看房预约',
                'member_id'     => $value['member_id'],
                'member_name'   => $value['name'],
                'member_mobile' => $value['mobile'],
            ];
        }
        return $result_list;
    }
}
            