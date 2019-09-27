<?php
namespace App\Services\Shop;


use App\Repositories\OaAuditTypeRepository;
use App\Services\BaseService;

class AuditService extends BaseService
{

    public function addAudit(array $data)
    {
        unset($data['sign'],$data['token']);
        if (OaAuditTypeRepository::exists(['name' => $data['name']])){
            return ['code' => 1,'message' => '审核类型名称已存在！'];
        }
        if (OaAuditTypeRepository::exists(['name' => $data['url']])){
            return ['code' => 1,'message' => '审核类型已存在！'];
        }
        $data['created_at'] = time();
        if(!OaAuditTypeRepository::getAddId($data)){
            return ['code' => 1,'message' => '添加失败,请重试！'];
        }
        return ['code' => 200,'message' => '添加成功'];
    }
}
            