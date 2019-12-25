<?php
namespace App\Services\Project;


use App\Repositories\ProjectOrderRepository;
use App\Services\BaseService;

class OrderService extends BaseService
{
    /**
     * 获取申请人ID
     * @param $project_order_id
     * @return mixed
     */
    public function getCreatedUser($project_order_id){
        return ProjectOrderRepository::getField(['id',$project_order_id],'user_id');
    }
}
            