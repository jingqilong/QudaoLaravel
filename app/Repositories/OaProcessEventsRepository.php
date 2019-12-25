<?php


namespace App\Repositories;


use App\Models\OaProcessEventsModel;
use App\Repositories\Traits\RepositoryTrait;

class OaProcessEventsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(OaProcessEventsModel $model)
    {
        $this->model = $model;
    }

    /**
     * @param $process_event_id
     * @return mixed;
     */
    protected function isEnabled($process_event_id){
        $action = $this->getOne(['id'=>$process_event_id]);
        if(!$action){
            return ['code'=>100,'message'=>"抱歉，此流程事件仍未定义！"];
        }
        if(2==$action['status']){
            return ['code'=>100,'message'=>"抱歉，此流程事件已被禁用！"];
        }
        return ['code'=>200,'message'=>"此流程事件存在且可用！"];
    }
}
            