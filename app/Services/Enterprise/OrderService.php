<?php
namespace App\Services\Enterprise;



use App\Repositories\EnterpriseOrderRepository;
use App\Services\BaseService;
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

        $page           = $data['page'] ?? 1;
        $page_num       = $data['page_num'] ?? 20;
        $keywords       = $data['keywords'] ?? null;
        $where = ['name' => $memberInfo['m_cname'],'mobile' => $memberInfo['m_phone'],'status' => ['in',[1,2,3,4]]];

        $column = ['id','name','mobile','enterprise_name','service_type','remark','status','reservation_at','created_at','updated_at'];
        if (!empty($keywords)){
            $keyword = [$keywords => ['enterprise_name','service_type']];
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
        unset($list['first_page_url'], $list['from'],
              $list['last_page_url'],  $list['from'],
              $list['next_page_url'],  $list['path'],
              $list['prev_page_url'],  $list['to']);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        foreach ($list['data'] as &$value)
        {
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
        if (!$list = EnterpriseOrderRepository::getOne(['id' => $id,'status' => ['in',[1,2,3,4]]])){
            $this->setError('查询不到该条数据！');
            return false;
        }

        $list['reservation_at']    =   date('Y-m-d H:m:s',$list['reservation_at']);
        $list['created_at']        =   date('Y-m-d H:m:s',$list['created_at']);
        $list['updated_at']        =   date('Y-m-d H:m:s',$list['updated_at']);

        $this->setMessage('查找成功');
        return $list;
    }


    /**
     * 添加项目订单信息
     * @param array $data
     * @return mixed
     */
    public function addEnterprise(array $data)
    {
        unset($data['sign'], $data['token']);
        $data['created_at']     = time();
        $data['reservation_at'] = strtotime($data['reservation_at']);
        $data['status']         = '1';

        if (!$res = EnterpriseOrderRepository::getAddId($data)){
            $this->setError('预约失败,请重试！');
            return false;
        }
        $this->setMessage('恭喜你，预约成功');
        return true;
    }

    /**
     * 修改项目订单信息
     * @param array $data
     * @return mixed
     */
    public function updEnterprise(array $data)
    {
        $id = $data['id'];
        unset($data['sign'], $data['token'], $data['id']);
        $data['updated_at']     = time();
        $data['reservation_at'] = strtotime($data['reservation_at']);
        $data['status']         = '1';  // 修改数据后  状态值从新开始

        if (!$res = EnterpriseOrderRepository::getUpdId(['id' => $id],$data)){
            $this->setError('修改失败,请重试！');
            return false;
        }
        $this->setMessage('恭喜你，修改成功');
        return true;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function delEnterprise(string $id)
    {
        if (!$EnterpriseInfo = EnterpriseOrderRepository::getOne(['id' => $id])){
            $this->setError('没有查找到该数据,请重试！');
            return false;
        }
        if (!$res = EnterpriseOrderRepository::getUpdId(['id' => $id],['status' => '9'])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功');
        return true;
    }
}
            