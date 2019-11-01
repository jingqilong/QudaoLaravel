<?php
namespace App\Services\House;


use App\Enums\HouseEnum;
use App\Repositories\HouseDetailsRepository;
use App\Repositories\HouseReservationRepository;
use App\Services\BaseService;
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
        $page = $request['page'] ?? 1;
        $page_num = $request['page_num'] ?? 20;
        $where = ['id' => ['<>',0]];
        if (!empty($member_id)){
            $where['member_id'] = $member_id;
        }
        $column = ['id','house_id','name','mobile','time','memo','state'];
        if (!$list = HouseReservationRepository::getList($where,$column,'id','desc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        foreach ($list['data'] as &$value){
            $value['house'] = [];
            $value['state']= HouseEnum::getReservationStatus($value['state']);
            $value['time']  = date('Y-m-d H:i:s',$value['time']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            