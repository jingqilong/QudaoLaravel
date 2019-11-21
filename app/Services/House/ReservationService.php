<?php
namespace App\Services\House;


use App\Enums\HouseEnum;
use App\Enums\MemberEnum;
use App\Enums\MessageEnum;
use App\Repositories\HouseDetailsRepository;
use App\Repositories\HouseReservationRepository;
use App\Repositories\MemberRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use App\Services\Message\SendService;
use App\Traits\HelpTrait;

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
        $house_list = HouseDetailsRepository::getList(['id' => ['in',$house_ids]],['id','title','area_code','address','rent','tenancy']);
        foreach ($list['data'] as &$value){
            $value['house_title'] = '';
            $value['area_address'] = '';
            $value['rent'] = '';
            if ($house = $this->searchArray($house_list,'id',$value['house_id'])){
                $house = reset($house);
                $value['house_title']           = $house['title'];
                list($area_address,$lng,$lat)   = $this->makeAddress($house['area_code'],$house['address']);
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
        if ($member = MemberRepository::getOne(['m_id' => $reservation['member_id']])){
            $member_name = $reservation['name'];
            $member_name = $member_name . MemberEnum::getSex($member['m_sex']);
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
            if (!empty($member['m_phone'])){
                $smsService = new SmsService();
                $smsService->sendContent($member['m_phone'],$sms_template[$status]);
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
     * @param $house_id
     * @param $member_id
     * @return array|null
     */
    public function isReservationList($house_id, $member_id)
    {
        if (!$house = HouseDetailsRepository::exists(['id' => $house_id,'publisher' => HouseEnum::PERSON,'publisher_id' => $member_id])){
            $this->setError('房源不存在！');
            return false;
        }
        if (!$house_list = HouseReservationRepository::getList(['house_id' => $house_id,'state' => HouseEnum::RESERVATIONOK],['id','time'])){
            $this->setMessage('暂无预约！');
            return [];
        }
        foreach ($house_list as &$value){
            $value['time']  = date('Y-m-d H:i:s',$value['time']);
        }
        $this->setMessage('获取成功！');
        return $house_list;
    }
}
            