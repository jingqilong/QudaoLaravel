<?php
namespace App\Services\Shop;


use App\Repositories\OaAuditTypeRepository;
use App\Services\BaseService;

class AuditService extends BaseService
{

    /**
     * @param array $data
     * @return array
     * @param 添加审核
     */
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

    /**
     * @param array $data
     * @return array
     * @param 删除审核类型
     */
    public function delAudit(array $data)
    {
        if (!OaAuditTypeRepository::exists(['id' => $data['id']])){
            return ['code' => 1,'message' => '未找到审核类型ID！'];
        }
        if(!OaAuditTypeRepository::delete(['id' => $data['id'],'name' => $data['name']])){
            return ['code' => 1,'message' => '删除失败,请重试！'];
        }
        return ['code' => 200,'message' => '删除成功'];
    }
}
            