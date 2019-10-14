<?php
namespace App\Services\Loan;


use App\Repositories\EnterpriseOrderRepository;
use App\Services\BaseService;

class PersonalService extends BaseService
{

    /**
     * 获取贷款订单列表 （前端显示）
     * @param array $data
     * @return mixed
     */
    public function getLoanList(array $data)
    {
        if (!$list = EnterpriseOrderRepository::getList(['name' => $data['name'],'type' => $data['type'],'status' => ['in',[1,2,3,4]]])){
            $this->setMessage('没有数据！');
            return [];
        }
        $this->setMessage('查找成功');
        return $list;
    }

    /**
     * 获取贷款订单信息
     * @param array $data
     * @return mixed
     */
    public function getLoanInfo(array $data)
    {
        if (!$list = EnterpriseOrderRepository::getOne(['id' => $data['id'],'type' => $data['type'],'status' => ['in',[1,2,3,4]]])){
            $this->setError('没有查到数据！');
            return false;
        }
        $list['reservation_at']    =   date('Y-m-d H:m:s',$list['reservation_at']);
        $list['created_at']        =   date('Y-m-d H:m:s',$list['created_at']);
        $list['updated_at']        =   date('Y-m-d H:m:s',$list['updated_at']);
        $this->setMessage('查找成功');
        return $list;
    }


    /**
     * 添加贷款订单信息
     * @param array $data
     * @return mixed
     */
    public function addLoan(array $data)
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
     * 修改贷款订单信息
     * @param array $data
     * @return mixed
     */
    public function updLoan(array $data)
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
     * 软删除订单
     * @param integer $id
     * @return mixed
     */
    public function delLoan($id)
    {
        if (!$loanInfo = EnterpriseOrderRepository::getOne(['id' => $id])){
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
            