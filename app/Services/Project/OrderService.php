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
    /**
     * 返回流程中的业务列表
     * @param $order_ids
     * @return mixed
     */
    public function getProcessBusinessList($order_ids){
        if (empty($order_ids)){
            return [];
        }
        $column     = ['id','user_id','name','mobile','project_name'];
        if (!$order_list = ProjectOrderRepository::getAssignList($order_ids,$column)){
            return [];
        }
        $result_list = [];
        foreach ($order_list as $value){
            $result_list[] = [
                'id'            => $value['id'],
                'name'          => $value['project_name'],
                'member_id'     => $value['user_id'],
                'member_name'   => $value['name'],
                'member_mobile' => $value['mobile'],
            ];
        }
        return $result_list;
    }
}
            