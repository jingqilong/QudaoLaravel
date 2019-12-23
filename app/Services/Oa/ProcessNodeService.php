<?php
namespace App\Services\Oa;


use App\Repositories\OaProcessActionPrincipalsRepository;
use App\Repositories\OaProcessActionRelatedRepository;
use App\Repositories\OaProcessDefinitionRepository;
use App\Repositories\OaProcessNodeActionRepository;
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
        if (empty($request['action_related_id']) && OaProcessNodeRepository::exists(['process_id' => $request['process_id'], 'position' => 1])){
            $this->setError('第一步只能添加一个节点！');
            return false;
        }
        $position = 1;
        if (!empty($request['action_related_id'])){
            if (!$action_related = OaProcessActionRelatedRepository::getOne(['id' => $request['action_related_id']])){
                $this->setError('节点动作相关不存在！');
                return false;
            }
            if (!empty($action_related['transition_id'])){
                $this->setError('当前节点动作已添加流转，无法添加！');
                return false;
            }
            if (!$node_id = OaProcessNodeActionRepository::getField(['id' => $action_related['node_action_id']],'node_id')){
                $this->setError('数据异常！');
                Loggy::write('error','给流程添加节点：数据异常，节点动作丢失！节点动作记录ID：'.$action_related['action_related_id']);
                return false;
            }
            if (!$node_position = OaProcessNodeRepository::getField(['id' => $node_id],'position')){
                $this->setError('数据异常！');
                Loggy::write('error','给流程添加节点：数据异常，节点丢失！节点ID：'.$node_id);
                return false;
            }
            $position = $node_position + 1;
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
            DB::rollBack();
            return false;
        }
        if (!OaProcessDefinitionRepository::getUpdId(['id' => $request['process_id']],['step_count' => $position])){
            $this->setError('添加失败！');
            DB::rollBack();
            return false;
        }
        if (!empty($request['action_related_id'])){
            $add_transition = [
                'process_id'    => $request['process_id'],
                'current_node'  => $node_id,
                'next_node'     => $new_node_id,
                'created_at'    => time(),
                'updated_at'    => time(),
            ];
            if (!$transition_id = OaProcessTransitionRepository::getAddId($add_transition)){
                $this->setError('添加失败！');
                DB::rollBack();
                return false;
            }
            $upd_action_related = ['transition_id' => $transition_id, 'updated_at' => time()];
            if (!OaProcessActionRelatedRepository::getUpdId(['id' => $request['action_related_id']],$upd_action_related)){
                $this->setError('添加失败！');
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
        /**
         * 删除节点步骤
         * 1，检查节点有效性
         * 2，检查该流程有没有使用，已使用的流程是无法删除节点的
         * 3，检查更新节点步骤，不是末端节点，无法进行删除动作
         * 4，获取要删除数据的索引
         * 5，解除当前节点与上一节点之间的流转关系
         * 6，删除节点动作相关流转
         * 7，删除节点动作相关
         * 8，删除节点动作负责人
         * 9，删除节点动作
         * 10，删除节点
         */
        #1、检查节点有效性
        if (!$process = OaProcessDefinitionRepository::getOne(['id' => $process_id])){
            $this->setError('该流程不存在！');
            return false;
        }
        if (!$node = OaProcessNodeRepository::getOne(['id' => $node_id,'process_id' => $process_id])){
            $this->setError('该节点不存在！');
            return false;
        }
        #2、检查该流程有没有使用，已使用的流程是无法删除节点的
        if (OaProcessRecordRepository::exists(['process_id' => $process_id])){
            $this->setError('当前流程已被使用，无法进行删除动作！');
            return false;
        }
        #3、检查更新节点步骤
        $node_list = OaProcessNodeRepository::getList(['process_id' => $process_id,'id' => ['<>',$node_id]]);
        $positions = array_column($node_list,'position');
        $process_upd = ['updated_at' => time()];
        foreach ($positions as $position){
            if ($position > $node['position']){
                $this->setError('该节点不是末端节点，无法进行删除动作！');
                return false;
            }
            if ($position <  $node['position']){
                $process_upd['step_count'] = $position;
            }
        }
        DB::beginTransaction();
        if (!OaProcessDefinitionRepository::getUpdId(['id' => $process_id],$process_upd)){
            $this->setError('删除失败！');
            DB::rollBack();
            return false;
        }
        #4、获取要删除数据的索引
        $node_action_ids        = [];//节点动作关联ID
        $action_related_ids     = [];//节点动作相关关联ID
        $action_principal_ids   = [];//节点动作负责人关联ID
        $transition_ids         = [];//节点动作相关流转关联ID
        $last_transition_ids    = [];//节点上一节点相关流转ID
        if ($node_actions = OaProcessNodeActionRepository::getList(['node_id' => $node_id])){
            $node_action_ids = array_column($node_actions,'id');
            if ($node_action_related = OaProcessActionRelatedRepository::getList(['node_action_id' => ['in',$node_action_ids]])){
                $action_related_ids = array_column($node_action_related,'id');
                $transition_ids     = array_column($node_action_related,'transition_id');
            }
            if ($action_principals = OaProcessActionPrincipalsRepository::getList(['node_action_id' => ['in',$node_action_ids]])){
                $action_principal_ids = array_column($action_principals,'id');
            }
        }
        if ($last_transitions = OaProcessTransitionRepository::getList(['next_node' => $node_id])){
            $last_transition_ids = array_column($last_transitions,'id');
            $transition_ids = array_merge($transition_ids,$last_transition_ids);
        }
        #5、解除当前节点与上一节点之间的流转关系
        if (!empty($last_transition_ids) && !OaProcessActionRelatedRepository::update(['transition_id' => ['in',$last_transition_ids]],['transition_id' => 0])){
            $this->setError('删除失败！');
            DB::rollBack();
            return false;
        }
        #6、删除节点动作相关流转
        if (!empty($transition_ids) && !OaProcessTransitionRepository::delete(['id' => ['in',$transition_ids]])){
            $this->setError('删除失败！');
            DB::rollBack();
            return false;
        }
        #7、删除节点动作相关
        if (!empty($action_related_ids) && !OaProcessActionRelatedRepository::delete(['id' => ['in',$action_related_ids]])){
            $this->setError('删除失败！');
            DB::rollBack();
            return false;
        }
        #8、删除节点动作负责人
        if (!empty($action_principal_ids) && !OaProcessActionPrincipalsRepository::delete(['id' => ['in',$action_principal_ids]])){
            $this->setError('删除失败！');
            DB::rollBack();
            return false;
        }
        #9、删除节点动作
        if (!empty($node_action_ids) && !OaProcessNodeActionRepository::delete(['id' => ['in',$node_action_ids]])){
            $this->setError('删除失败！');
            DB::rollBack();
            return false;
        }
        #10、删除节点
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
        if (!OaProcessDefinitionRepository::exists(['id' => $request['process_id']])){
            $this->setError('该流程不存在！');
            return false;
        }
        if ($node['name'] != $request['name'] &&
            OaProcessNodeRepository::exists(['name' => $request['name'], 'process_id' => $request['process_id']])){
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
            