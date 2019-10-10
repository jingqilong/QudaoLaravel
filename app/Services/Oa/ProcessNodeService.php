<?php
namespace App\Services\Oa;


use App\Repositories\OaProcessActionRelatedRepository;
use App\Repositories\OaProcessDefinitionRepository;
use App\Repositories\OaProcessNodeActionRepository;
use App\Repositories\OaProcessNodeRepository;
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
         * 2，根据节点动作相关中关联的流转建立新的流转关系
         * 3，删除节点动作相关中关联的流转
         * 4，删除节点动作相关
         * 5，删除节点动作相关负责人
         * 6，删除节点动作
         */
//        #1、检查节点有效性
//        if (!$process = OaProcessDefinitionRepository::getOne(['id' => $process_id])){
//            $this->setError('该流程不存在！');
//            return false;
//        }
//        if (!$node = OaProcessNodeRepository::getOne(['id' => $node_id,'process_id' => $process_id])){
//            $this->setError('该节点不存在！');
//            return false;
//        }
//        DB::beginTransaction();
//        #2、根据节点动作相关中关联的流转建立新的流转关系
//        if ($node_actions = OaProcessNodeActionRepository::getList(['node_id' => $node_id])){
//            #当前节点的动作总数
//            $node_action_count = count($node_actions);
//            foreach ($node_actions as $action){
//                #获取流程节点相关的记录
//                if (!$node_action_relations = OaProcessActionRelatedRepository::getList(['node_action_id' => $action['id']])){
//                    continue;
//                }
//                #动作结果跳转节点数组
//                $action_dump_nodes = [];
//                #动作结果总数
//                $action_result_count = count($node_action_relations);
//                foreach ($node_action_relations as $related){
//                    $transition = OaProcessTransitionRepository::getOne(['id' => $related['transition_id']]);
//                }
//            }
//        }
//
//
//        if ($node_action_relations = OaProcessActionRelatedRepository::getList(['node_action_id' => $node_id])){
//            foreach ($node_action_relations as $node_action_related){
//                if ($transition = OaProcessTransitionRepository::getOne(['id' => $node_action_related['transition_id']])){
//
//                }
//            }
//        }
    }

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
}
            