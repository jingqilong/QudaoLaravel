<?php
namespace App\Services\Oa;


use App\Enums\ProcessTransitionStatusEnum;
use App\Repositories\OaProcessActionEventRepository;
use App\Repositories\OaProcessActionPrincipalsRepository;
use App\Repositories\OaProcessActionRelatedRepository;
use App\Repositories\OaProcessDefinitionRepository;
use App\Repositories\OaProcessNodeActionRepository;
use App\Repositories\OaProcessNodeActionsResultRepository;
use App\Repositories\OaProcessNodeRepository;
use App\Repositories\OaProcessRecordRepository;
use App\Repositories\OaProcessTransitionRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Tolawho\Loggy\Facades\Loggy;

class ProcessNodeService extends BaseService
{

    /**
     * 给流程添加节点
     * @param $request
     * @return bool
     */
    public function processAddNode($request)
    {
        if (!OaProcessDefinitionRepository::exists(['id' => $request['process_id']])){
            $this->setError('该流程不存在！');
            return false;
        }
        if (OaProcessNodeRepository::exists(['name' => $request['name'], 'process_id' => $request['process_id']])){
            $this->setError('节点名称已被占用！');
            return false;
        }
        #检查当前流程是否添加第一个节点
        $check_first_node = OaProcessNodeRepository::exists(['process_id' => $request['process_id'], 'position' => 1]);
        if (empty($request['node_actions_result_id']) && $check_first_node){
            $this->setError('节点动作结果ID不能为空！');
            return false;
        }
        $position = 1;
        if ($check_first_node && !empty($request['node_actions_result_id'])){#如果节点动作结果ID存在，则表示添加的节点需要关联到节点动作结果
            if (OaProcessTransitionRepository::exists(['process_id' => $request['process_id'],'node_action_result_id' => $request['node_action_result_id']])){
                $this->setError('当前节点动作结果已添加下一节点，无法添加！');
                return false;
            }
            if (!$node_actions_result = OaProcessNodeActionsResultRepository::getOne(['id' => $request['node_actions_result_id']])){
                $this->setError('节点动作相关不存在！');
                return false;
            }
            if (!$node_id = OaProcessNodeActionRepository::getField(['id' => $node_actions_result['node_action_id']],'node_id')){
                $this->setError('数据异常！');
                Loggy::write('error','给流程添加节点：数据异常，节点动作丢失！节点动作记录ID：'.$node_actions_result['action_related_id']);
                return false;
            }
            if (!$node_position = OaProcessNodeRepository::getField(['id' => $node_id],'position')){
                $this->setError('数据异常！');
                Loggy::write('error','给流程添加节点：数据异常，节点丢失！节点ID：'.$node_id);
                return false;
            }
            $position = $node_position + 1;//步骤
        }
        $add_arr = [
            'process_id'    => $request['process_id'],
            'name'          => $request['name'],
            'limit_time'    => $request['limit_time'] ?? 0,
            'icon'          => $request['icon'] ?? '',
            'position'      => $position,
            'description'   => $request['description'] ?? '',
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        DB::beginTransaction();
        if (!$new_node_id = OaProcessNodeRepository::getAddId($add_arr)){
            $this->setError('添加失败！');
            Loggy::write('error','给流程添加节点：数据插入失败！');
            DB::rollBack();
            return false;
        }
        if (!OaProcessDefinitionRepository::increment(['id' => $request['process_id']],'step_count')){
            $this->setError('添加失败！');
            Loggy::write('error','给流程添加节点：流程定义表步骤自增失败！');
            DB::rollBack();
            return false;
        }
        //如果节点动作结果ID存在，添加流转
        if (!empty($request['node_actions_result_id'])){
            $add_transition = [
                'process_id'            => $request['process_id'],
                'node_action_result_id' => $request['node_actions_result_id'],
                'current_node'          => $node_id,
                'next_node'             => $new_node_id,
                'status'                => ProcessTransitionStatusEnum::GO_ON,
                'created_at'            => time(),
                'updated_at'            => time(),
            ];
            if (!$transition_id = OaProcessTransitionRepository::getAddId($add_transition)){
                $this->setError('添加失败！');
                Loggy::write('error','给流程添加节点：流转添加失败！');
                DB::rollBack();
                return false;
            }
        }
        $this->setMessage('添加成功！');
        DB::commit();
        return true;
    }

    /**
     * 删除节点
     * @param $process_id
     * @param $node_id
     * @return mixed
     */
    public function deleteNode($process_id, $node_id)
    {
        #检查流程、节点有效性
        if (!$process = OaProcessDefinitionRepository::getOne(['id' => $process_id])){
            $this->setError('该流程不存在！');
            return false;
        }
        if (!$node = OaProcessNodeRepository::getOne(['id' => $node_id,'process_id' => $process_id])){
            $this->setError('该节点不存在！');
            return false;
        }
        #检查该流程有没有使用，已使用的流程是无法删除节点的
        if (OaProcessRecordRepository::exists(['process_id' => $process_id])){
            $this->setError('当前流程已被使用，无法进行删除动作！');
            return false;
        }
        #检查更新节点步骤，
        $new_step_count = null;#新的总步骤数，如果是null，则无需更新
        if (1 == OaProcessNodeRepository::count(['process_id' => $process_id,'id' => ['<>',$node_id]])){
            $new_step_count = $node['position'] - 1;
        }
        #如果当前节点还有下一节点，说明该节点不是末端节点，无法进行删除动作
        if (OaProcessTransitionRepository::exists(['current_node' => $node_id,'next_node' => ['<>',0]])){
            $this->setError('该节点不是末端节点，无法进行删除动作！');
            return false;
        }
        $process_upd = ['updated_at' => time(),'step_count' => $new_step_count];
        DB::beginTransaction();
        if (!is_null($new_step_count) && !OaProcessDefinitionRepository::getUpdId(['id' => $process_id],$process_upd)){
            $this->setError('删除失败！');
            DB::rollBack();
            return false;
        }
        #解除当前节点与上一节点之间的流转关系
        if (!OaProcessTransitionRepository::update(['next_node' => $node_id],['next_node' => 0])){
            $this->setError('删除失败！');
            Loggy::write('error','删除流程节点：解除当前节点与上一节点之间的流转关系失败！节点ID：'.$node_id);
            DB::rollBack();
            return false;
        }
        #删除节点动作流转
        if (OaProcessTransitionRepository::exists(['current_node' => $node_id]) &&
            !OaProcessTransitionRepository::delete(['current_node' => $node_id])){
            $this->setError('删除失败！');
            Loggy::write('error','删除流程节点：删除节点动作流转失败！节点ID：'.$node_id);
            DB::rollBack();
            return false;
        }
        #如果该节点有添加动作，删除相应的信息
        if ($node_action_list = OaProcessNodeActionRepository::getList(['node_id' => $node_id],['id'])){
            $node_action_ids = array_column($node_action_list,'id');
            #删除节点动作结果事件
            if (OaProcessActionEventRepository::exists(['node_action_id' => ['in' , $node_action_ids]]) &&
                !OaProcessActionEventRepository::delete(['node_action_id' => ['in' , $node_action_ids]])
            ){
                $this->setError('删除失败！');
                Loggy::write('error','删除流程节点：删除节点动作结果事件失败！节点ID：'.$node_id);
                DB::rollBack();
                return false;
            }
            #删除节点动作结果
            if (OaProcessNodeActionsResultRepository::exists(['node_action_id' => ['in' , $node_action_ids]]) &&
                !OaProcessNodeActionsResultRepository::delete(['node_action_id' => ['in' , $node_action_ids]])
            ){
                $this->setError('删除失败！');
                Loggy::write('error','删除流程节点：删除节点动作结果失败！节点ID：'.$node_id);
                DB::rollBack();
                return false;
            }
            #删除节点动作
            if (OaProcessNodeActionRepository::exists(['node_id' => $node_id]) &&
                !OaProcessNodeActionRepository::delete(['node_id' => $node_id])
            ){
                $this->setError('删除失败！');
                Loggy::write('error','删除流程节点：删除节点动作失败！节点ID：'.$node_id);
                DB::rollBack();
                return false;
            }
        }
        #删除节点
        if (!OaProcessNodeRepository::delete(['id' => $node_id])){
            $this->setError('删除失败！');
            DB::rollBack();
            return false;
        }
        $this->setMessage('删除成功！');
        DB::commit();
        return true;
    }

    /**
     * 修改节点
     * @param $request
     * @return bool
     */
    public function processEditNode($request)
    {
        if (!$node = OaProcessNodeRepository::getOne(['id' => $request['node_id']])){
            $this->setError('该节点不存在！');
            return false;
        }
        if ($node['name'] != $request['name'] &&
            OaProcessNodeRepository::exists(['name' => $request['name'], 'process_id' => $node['process_id']])){
            $this->setError('节点名称已被占用！');
            return false;
        }
        $upd_arr = [
            'process_id'    => $request['process_id'],
            'name'          => $request['name'],
            'limit_time'    => $request['limit_time'] ?? 0,
            'icon'          => $request['icon'] ?? '',
            'description'   => $request['description'] ?? '',
            'updated_at'    => time(),
        ];
        if (OaProcessNodeRepository::getUpdId(['id' => $request['node_id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * @desc 通过流程ID，以及用户ID获取用户在此流程中的哪一层有审核权限，返回一个ID
     * @param $process_id
     * @param $user_id
     * @return integer
     *
     */
    public function getStartNodeByUser($process_id,$user_id){
        //TODO 这里有业务逻辑要实现
        return 1;
    }
}
            