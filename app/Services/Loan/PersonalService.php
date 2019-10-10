<?php
namespace App\Services\Loan;


use App\Repositories\LoanPersonalRepository;
use App\Repositories\MemberRepository;
use App\Services\BaseService;

class PersonalService extends BaseService
{

    /**
     * @param array $data
     * @return bool|null
     * @param  获取贷款订单列表
     */
    public function getLoanList(array $data)
    {
        if (!$list = LoanPersonalRepository::getList(['name' => $data['name'],'type' => $data['type'],'status' => ['in',[1,2,3,4]]])){
            $this->setError('查找失败！');
            return false;
        }
        $this->setMessage('查找成功');
        return $list;
    }


    /**
     * @param array $data
     * @return mixed
     * @param 添加贷款订单信息
     */
    public function addLoan(array $data)
    {
        unset($data['sign'], $data['token']);
        $data['created_at']     = time();
        $data['reservation_at'] = strtotime($data['reservation_at']);
        $data['status']         = '1';

        if (!$res = LoanPersonalRepository::getAddId($data)){
            $this->setError('预约失败,请重试！');
            return false;
        }
        $this->setMessage('恭喜你，预约成功');
        return true;
    }

    /**
     * @param array $data
     * @return mixed
     * @param 修改贷款订单信息
     */
    public function updLoan(array $data)
    {
        $id = $data['id'];
        unset($data['sign'], $data['token'], $data['id']);
        $data['updated_at']     = time();
        $data['reservation_at'] = strtotime($data['reservation_at']);
        $data['status']         = '1';  // 修改数据后  状态值从新开始

        if (!$res = LoanPersonalRepository::getUpdId(['id' => $id],$data)){
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
    public function delLoan(string $id)
    {
        if (!$loanInfo = LoanPersonalRepository::getOne(['id' => $id])){
            $this->setError('没有查找到该数据,请重试！');
            return false;
        }
        if (!$res = LoanPersonalRepository::getUpdId(['id' => $id],['status' => '9'])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功');
        return true;
    }
}
            