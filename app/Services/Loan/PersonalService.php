<?php
namespace App\Services\Loan;


use App\Repositories\LoanPersonalRepository;
use App\Services\BaseService;

class PersonalService extends BaseService
{
    /**
     * @param array $data
     * @return array
     * @param 添加贷款订单信息
     */
    public function add(array $data)
    {
        $data['created_at'] = time();
        if (!$res = LoanPersonalRepository::getAddId($data)){
            return ['code' => 1,'message' => '添加失败,请重试！'];
        }
        return ['code' => 200,'message' => '添加成功'];
    }

    public function update(string $id)
    {
        if (!$res = LoanPersonalRepository::getOne($id)){
            return ['code' => 1,'message' => '查找失败,请重试！'];
        }
    }
}
            