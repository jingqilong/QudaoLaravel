<?php


namespace App\Repositories;


use App\Models\OaProcessActionPrincipalsModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessActionPrincipalsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessActionPrincipalsModel $model)
    {
        $this->model = $model;
    }

    /**
     * 使用节点动作ID删除节点动作结果
     * @param $node_action_id
     * @return bool
     */
    protected function deleteByActionResult($node_action_id){
        if (!$this->exists(['node_action_id' => $node_action_id])){
            return true;
        }
        if (!$this->delete(['node_action_id' => $node_action_id])){
            return false;
        }
        return true;
    }
}
            