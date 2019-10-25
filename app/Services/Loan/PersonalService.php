<?php
namespace App\Services\Loan;


use App\Enums\LoanEnum;
use App\Repositories\LoanPersonalRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class PersonalService extends BaseService
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
     * 获取贷款订单列表 （前端显示）
     * @param array $data
     * @return mixed
     */
    public function getLoanList(array $data)
    {
        $memberInfo = $this->auth->user();
        if (!$list = LoanPersonalRepository::getList(['user_id' => $memberInfo['m_id'],'type' => $data['type'],'status' => ['in',[1,2,3,4]]])){
            $this->setMessage('没有数据！');
            return [];
        }
        foreach ($list as &$value)
        {
            $value['reservation_at']    =   date('Y-m-d H:m:s',$value['reservation_at']);
            $value['created_at']        =   date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']        =   date('Y-m-d H:m:s',$value['updated_at']);
        }
        $this->setMessage('查找成功');
        return $list;
    }


    /**
     * 获取贷款订单列表 （后台显示）
     * @param array $data
     * @return mixed
     */
    public function getLoanOrderList(array $data)
    {
        $page           = $data['page'] ?? 1;
        $asc            = $data['asc'] ==  1 ? 'asc' : 'desc';
        $page_num       = $data['page_num'] ?? 20;
        $column         = ['*'];
        $where          = ['status' => ['in',[LoanEnum::SUBMITTED,LoanEnum::INREVIEW,LoanEnum::PASS,LoanEnum::FAILURE]]];
        if (!$list = LoanPersonalRepository::getList($where,$column,'id',$asc,$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);

        foreach ($list['data'] as &$value)
        {
            $value['type_name']         =   empty($value['type']) ? '' : LoanEnum::getType($value['type']);
            $value['status_name']       =   empty($value['status']) ? '' : LoanEnum::getStatus($value['status']);
            $value['reservation_at']    =   date('Y-m-d H:m:s',$value['reservation_at']);
            $value['created_at']        =   date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']        =   date('Y-m-d H:m:s',$value['updated_at']);
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
        if (!$list = LoanPersonalRepository::getOne(['id' => $data['id'],'type' => $data['type'],'status' => ['in',[1,2,3,4]]])){
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
        $memberInfo = $this->auth->user();

        unset($data['sign'], $data['token']);
        $data['user_id']        = $memberInfo['m_id'];
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

        if (!$res = LoanPersonalRepository::getUpdId(['id' => $id],$data)){
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

    /**
     * 根据ID查找贷款订单信息
     * @param array $data
     * @return bool|null
     */
    public function getLoanOrderInfo(array $data)
    {
        if (!LoanPersonalRepository::exists(['id' => $data['id']])){
            $this->setError('没有该订单！');
            return false;
        }
        if (!$list = LoanPersonalRepository::getOne(['id' => $data['id']])){
            $this->setError('没有查到该订单信息！');
            return false;
        }
        $list['type_name']         =    empty($list['type']) ? '' : LoanEnum::getType($list['type']);
        $list['status_name']       =    empty($list['status']) ? '' : LoanEnum::getStatus($list['status']);
        $list['reservation_at']    =    date('Y-m-d H:m:s',$list['reservation_at']);
        $list['created_at']        =    date('Y-m-d H:m:s',$list['created_at']);
        $list['updated_at']        =    date('Y-m-d H:m:s',$list['updated_at']);
        $this->setMessage('查找成功');
        return $list;
    }
}
            