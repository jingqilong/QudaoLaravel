<?php


namespace App\Repositories;


use App\Models\OaProcessActionsModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessActionsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessActionsModel $model)
    {
        $this->model = $model;
    }

    /**
     * @param $action_id
     * @return mixed;
     */
    protected function isEnabled($action_id){
        $action = $this->getOne(['id'=>$action_id]);
        if(!$action){
            return ['code'=>100,'message'=>"抱歉，此流程动作仍未定义！"];
        }
        if(2==$action['status']){
            return ['code'=>100,'message'=>"抱歉，此流程动作已被禁用！"];
        }
        return ['code'=>200,'message'=>"此流程动作存在且可用！"];
    }
}
            