<?php


namespace App\Repositories;


use App\Models\OaProcessDefinitionModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessDefinitionRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessDefinitionModel $model)
    {
        $this->model = $model;
    }

    /**
     * @param $process_id
     * @return mixed;
     */
    protected function isEnabled($process_id){
        $process = $this->getOne(['id'=>$process_id]);
        if(!$process){
            return ['code'=>100,'message'=>"抱歉，此流程仍未定义！"];
        }
        if(2==$process['status']){
            return ['code'=>100,'message'=>"抱歉，此流程已被禁用！"];
        }
        return ['code'=>200,'message'=>"此流程存在且可用！"];
    }

    /**
     * 刷新流程总步骤数
     * @param $process_id
     * @return |null
     */
    protected function updateStepCount($process_id){
        $node = OaProcessNodeRepository::getOrderOne(['process_id' => $process_id],'position','desc');
        return $this->update(['id' => $process_id],['step_count' => $node['position'],'updated_at' => time()]);
    }
}
            