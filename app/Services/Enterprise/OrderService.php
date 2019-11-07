<?php
namespace App\Services\Enterprise;



use App\Enums\EnterEnum;
use App\Repositories\EnterpriseOrderRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class OrderService extends BaseService
{

    use HelpTrait;
    public $auth;

    /**
     * PrizeService constructor.
     * @param $auth
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }

    /**
     * 获取项目对接订单列表  （前端使用）
     * @param array $data
     * @return mixed
     */
    public function getEnterpriseList(array $data)
    {
        $memberInfo = $this->auth->user();

        $where  = ['deleted_at' => 0,'user_id' => $memberInfo['m_id']];
        $column = ['id','name','mobile','enterprise_name','service_type','remark','status','reservation_at','created_at','updated_at','deleted_at'];
        if (!$list = EnterpriseOrderRepository::getList($where,$column,'created_at','desc')){
            $this->setMessage('没有数据！');
            return [];
        }
        if (empty($list['data'])){
            $this->setMessage('没有数据！');
            return $list;
        }
        $list = $this->removePagingField($list);
        foreach ($list['data'] as &$value)
        {
            $value['type_name']         =   EnterEnum::getType($value['service_type']);
            $value['status_name']       =   EnterEnum::getStatus($value['status']);
            $value['reservation_at']    =   date('Y-m-d H:m:s',$value['reservation_at']);
            $value['created_at']        =   date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']        =   date('Y-m-d H:m:s',$value['updated_at']);
        }
        $this->setMessage('查找成功');
        return $list;
    }


 /**
     * 获取项目对接订单列表  （后端使用）
     * @param array $data
     * @return mixed
     */
    public function getEnterpriseOrderList(array $data)
    {

        $page           = $data['page'] ?? 1;
        $page_num       = $data['page_num'] ?? 20;
        $keywords       = $data['keywords'] ?? null;
        $type           = $data['type'] ?? null;
        $where          = ['deleted_at' => 0];

        $column = ['id','name','mobile','enterprise_name','service_type','remark','status','reservation_at','created_at','updated_at','deleted_at'];
        if ($type !== null){
            $where['service_type'] = $type;
        }
        if (!empty($keywords)){
            $keyword = [$keywords => ['enterprise_name']];
            if (!$list = EnterpriseOrderRepository::search($keyword,$where,$column,$page,$page_num)){
                $this->setMessage('没有数据！');
                return [];
            }
        }else{
            if (!$list = EnterpriseOrderRepository::search([],$where,$column,$page,$page_num,'created_at','desc')){
                $this->setMessage('没有数据！');
                return [];
            }
        }
        $list = $this->removePagingField($list);

        if (empty($list['data'])){
            $this->setMessage('没有获取到数据！');
            return $list;
        }
        foreach ($list['data'] as &$value)
        {
            $value['type_name']         =   EnterEnum::getType($value['service_type']);
            $value['status_name']       =   EnterEnum::getStatus($value['status']);
            $value['reservation_at']    =   date('Y-m-d H:m:s',$value['reservation_at']);
            $value['created_at']        =   date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']        =   date('Y-m-d H:m:s',$value['updated_at']);
        }
        $this->setMessage('查找成功');
        return $list;
    }

    /**
     * 获取项目对接订单详情
     * @param string $id
     * @return mixed
     */
    public function getEnterpriseInfo(string $id)
    {
        if (!$list = EnterpriseOrderRepository::getOne(['id' => $id,'deleted_at' => 0])){
            $this->setError('查询不到该条数据！');
            return false;
        }
        $list['type_name']         =   EnterEnum::getType($list['service_type']);
        $list['status_name']       =   EnterEnum::getStatus($list['status']);
        $list['reservation_at']    =   date('Y-m-d H:m:s',$list['reservation_at']);
        $list['created_at']        =   date('Y-m-d H:m:s',$list['created_at']);
        $list['updated_at']        =   date('Y-m-d H:m:s',$list['updated_at']);

        $this->setMessage('查找成功');
        return $list;
    }


    /**
     * 添加项目订单信息 前端
     * @param array $data
     * @return mixed
     */
    public function addEnterprise(array $data)
    {
        $memberInfo = $this->auth->user();
        $status                 = EnterEnum::SUBMIT;
        $add_arr = [
            'user_id'           => $memberInfo['m_id'],
            'name'              => $data['name'],
            'mobile'            => $data['mobile'],
            'enterprise_name'   => $data['enterprise_name'],
            'service_type'      => $data['service_type'],
            'remark'            => $data['remark'],
            'status'            => $status,
            'created_at'        => time(),
            'reservation_at'    => strtotime($data['reservation_at']),
        ];
        if (!$res = EnterpriseOrderRepository::getAddId($add_arr)){
            $this->setError('预约失败,请重试！');
            return false;
        }
        $this->setMessage('恭喜你，预约成功');
        return true;
    }

    /**
     * 修改项目订单信息 后端修改
     * @param array $data
     * @return mixed
     */
    public function updOrderEnterprise(array $data)
    {
        $id = $data['id'];

        if (!$enterpriseInfo = EnterpriseOrderRepository::getOne(['id' => $id])){
            $this->setError('没有找到该订单！');
            return false;
        }
        $status                 =  EnterEnum::SUBMIT;
        $upd_arr = [
            'name'              => $data['name'],
            'mobile'            => $data['mobile'],
            'enterprise_name'   => $data['enterprise_name'],
            'service_type'      => $data['service_type'],
            'remark'            => $data['remark'],
            'status'            => $status,
            'updated_at'        => time(),
            'reservation_at'    => strtotime($data['reservation_at']),
        ];

        if (!EnterpriseOrderRepository::getUpdId(['id' => $id],$upd_arr)){
            $this->setError('修改失败！');
            return false;
        }
        $this->setMessage('恭喜你，修改成功');
        return true;
    }

    /**
     * 删除审核订单
     * @param string $id
     * @return mixed
     */
    public function delEnterprise(string $id)
    {
        if (!$EnterpriseInfo = EnterpriseOrderRepository::exists(['id' => $id])){
            $this->setError('没有找到该数据！');
            return false;
        }
        if (!EnterpriseOrderRepository::getUpdId(['id' => $id],['deleted_at' => time()])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功');
        return true;
    }

    /**
     * 审核预约列表状态(oa)
     * @param $request
     * @return bool|null
     */
    public function setDoctorOrder($request)
    {

        if (!EnterpriseOrderRepository::exists(['id' => $request['id']])){
            $this->setError('无此订单!');
            return false;
        }
        if (!$orderInfo = EnterpriseOrderRepository::getOne(['id' => $request['id']])){
            $this->setError('无此订单!');
            return false;
        }
        $upd_arr = [
            'status'      => $request['status'] == 1 ? EnterEnum::PASS : EnterEnum::NOPASS,
            'updated_at'  => time(),
        ];

        if ($updOrder = EnterpriseOrderRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            if ($request['status'] == EnterEnum::PASS){
                //TODO 此处可以添加报名后发通知的事务
                #发送短信
                if (!empty($orderInfo)){
                    $sms = new SmsService();
                    $content = '您好！您预约的《'.$orderInfo['enterprise_name'].'》项目,已通过审核,我们将在24小时内负责人联系您,请保持消息畅通，谢谢！';
                    $sms->sendContent($orderInfo['mobile'],$content);
                }
                $this->setMessage('审核通过,消息已发送给联系人！');
                return true;
            }
            //TODO 此处可以添加报名后发通知的事务
            #发送短信
            if (!empty($orderInfo)){
                $sms = new SmsService();
                $content = '您好！您预约的《'.$orderInfo['enterprise_name'].'》未通过审核,请您联系客服0000-00000再次预约，谢谢！';
                $sms->sendContent($orderInfo['mobile'],$content);
            }
            $this->setError('审核成功,状态《未通过》，消息已发送给联系人！');
            return false;
        }
        $this->setError('审核失败，请重试!');
        return false;
    }
}
            