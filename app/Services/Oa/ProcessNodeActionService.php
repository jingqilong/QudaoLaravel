<?php
namespace App\Services\Oa;


use App\Enums\ProcessEventEnum;
use App\Repositories\OaProcessActionPrincipalsRepository;
use App\Repositories\OaProcessActionRelatedRepository;
use App\Repositories\OaProcessActionsRepository;
use App\Repositories\OaProcessActionsResultRepository;
use App\Repositories\OaProcessEventsRepository;
use App\Repositories\OaProcessNodeActionRepository;
use App\Repositories\OaProcessNodeActionsResultRepository;
use App\Repositories\OaProcessNodeRepository;

use App\Repositories\OaProcessTransitionRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\DB;
use Tolawho\Loggy\Facades\Loggy;

class ProcessNodeActionService extends BaseService
{
    use HelpTrait;

    /**
     * 给流程节点添加动作
     * @param $node_id
     * @param $action_id
     * @return bool
     */
    public function nodeAddAction($node_id, $action_id)
    {
        /**
         * 添加步骤
         * 1，检查值有效性
         * 2，处理要添加的数据
         * 2，添加流程节点动作
         * 3，添加流程节点动作结果
         */
        if (!OaProcessNodeRepository::exists(['id' => $node_id])){
            $this->setError('该节点不存在！');
            return false;
        }
        if (!$action = OaProcessActionsRepository::getOne(['id' => $action_id])){
            $this->setError('该动作不存在！');
            return false;
        }
        if (OaProcessNodeActionRepository::exists(['node_id' => $node_id, 'action_id' => $action_id])){
            $this->setError('该动作已添加，请勿重复添加！');
            return false;
        }
        if (!$results = OaProcessActionsResultRepository::getList(['action_id' => $action_id])){
            $this->setError('该动作没有执行结果，无法添加！');
            return false;
        }
        $add_node_action = [
            'node_id'       => $node_id,
            'action_id'     => $action_id,
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        DB::beginTransaction();
        if (!$node_action_id = OaProcessNodeActionRepository::getAddId($add_node_action)){
            $this->setError('添加动作失败！');
            DB::rollBack();
            return false;
        }
        $add_node_actions_result    = [];
        $time                       = time();
        foreach ($results as $result){
            $add_node_actions_result[] = [
                'node_action_id'    => $node_action_id,
                'action_result_id'  => $result['id'],
                'created_at'        => $time,
                'updated_at'        => $time,
            ];
        }
        if (!OaProcessNodeActionsResultRepository::create($add_node_actions_result)){
            $this->setError('添加动作失败！');
            Loggy::write('error','流程节点添加动作失败！原因：节点动作添加完成，但是添加节点动作结果时未能添加成功！节点ID：'.$node_id.'，动作ID：'.$action_id);
            DB::rollBack();
            return false;
        }
        DB::commit();
        $this->setMessage('添加成功！');
        return true;
    }

    /**
     * 给节点动作添加事件
     * @param $request
     * @return bool
     * @deprecated true
     */
    public function actionAddRelated($request)
    {
        if (!$action_related = OaProcessActionRelatedRepository::getOne(['id' => $request['action_related_id']])){
            $this->setError('节点动作相关不存在！');
            return false;
        }
        DB::beginTransaction();
        if (!empty($request['event_ids'])){
            $event_ids = explode(',',$request['event_ids']);
            $event_count = OaProcessEventsRepository::count(['id' => ['in', $event_ids],'status' => ProcessEventEnum::ENABLE]);
            if ($event_count != count($event_ids)){
                $this->setError('存在无效事件！');
                DB::rollBack();
                return false;
            }
            if (!OaProcessActionRelatedRepository::getUpdId(
                ['id' => $request['action_related_id']],
                ['event_ids' => $request['event_ids'],'updated_at' => time()])){
                $this->setError('添加失败！');
                DB::rollBack();
                return false;
            }
        }
        if (isset($request['next_node_id'])){
            if ($action_related['transition_id'] > 0){
                $this->setError('下一节点已添加，请勿重复添加！');
                return false;
            }
            if ($request['next_node_id'] != 0 && !$node = OaProcessNodeRepository::getOne(['id' => $request['next_node_id']])){
                $this->setError('下一节点不存在！');
                DB::rollBack();
                return false;
            }
            if (!$now_node_id = OaProcessNodeActionRepository::getField(['id' => $action_related['node_action_id']],'node_id')){
                $this->setError('数据异常！');
                Loggy::write('error','给流程节点添加下一节点：数据异常，节点动作丢失！节点动作记录ID：'.$request['action_related_id']);
                DB::rollBack();
                return false;
            }
            if ($now_node_id == $request['next_node_id']){
                $this->setError('不能添加自身为下一节点！');
                DB::rollBack();
                return false;
            }
            $now_process_id = OaProcessNodeRepository::getField(['id'=>$now_node_id],'process_id');
            if ($request['next_node_id'] != 0){
                if ($node['process_id'] != $now_process_id){
                    $this->setError('该节点不属于本流程！');
                    DB::rollBack();
                    return false;
                }
            }
            $add_transition = [
                'process_id'    => $now_process_id,
                'current_node'  => $now_node_id,
                'next_node'     => $request['next_node_id'],
                'created_at'    => time(),
                'updated_at'    => time(),
            ];
            if (!$transition_id = OaProcessTransitionRepository::getAddId($add_transition)){
                $this->setError('添加失败！');
                DB::rollBack();
                return false;
            }
            if (!OaProcessActionRelatedRepository::getUpdId(
                ['id' => $request['action_related_id']],
                ['transition_id' => $transition_id,'updated_at' => time()])){
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
     * 删除流程节点中的动作
     * @param integer $node_action_id      节点动作ID
     * @return bool
     */
    public function nodeDeleteAction($node_action_id)
    {
        if (!OaProcessNodeActionRepository::exists(['id' => $node_action_id])){
            $this->setError('该节点动作不存在！');
            return false;
        }
        DB::beginTransaction();
        if ($node_actions_results = OaProcessNodeActionsResultRepository::getList(['node_action_id' => $node_action_id])){
            $node_actions_result_ids = array_column($node_actions_results,'id');
            if (OaProcessTransitionRepository::exists(['node_action_result_id' => ['in',$node_actions_result_ids],'next_node' => ['<>',0]])){
                $this->setError('当前动作的结果已关联到下一节点，需要先删除下一节点！');
                DB::rollBack();
                return false;
            }
            #删除节点动作结果相关的流转
            if (OaProcessTransitionRepository::delete(['node_action_result_id' => ['in',$node_actions_result_ids]])){
                $this->setError('删除失败！');
                DB::rollBack();
                Loggy::write('error','流程节点删除动作：删除节点动作结果相关的流转失败！节点动作ID：'.$node_action_id);
                return false;
            }
        }
        #删除节点动作结果
        if (!OaProcessNodeActionsResultRepository::delete(['node_action_id' => $node_action_id])){
            $this->setError('删除失败！');
            DB::rollBack();
            Loggy::write('error','流程节点删除动作：删除节点动作结果失败！节点动作ID：'.$node_action_id);
            return false;
        }
        #删除动作下负责人记录
        if (OaProcessActionPrincipalsRepository::exists(['node_action_id' => $node_action_id]))
        if (!OaProcessActionPrincipalsRepository::delete(['node_action_id' => $node_action_id])){
            $this->setError('删除失败！');
            DB::rollBack();
            Loggy::write('error','流程节点删除动作：删除节点动作负责人失败！节点动作ID：'.$node_action_id);
            return false;
        }
        #删除动作
        if (!OaProcessNodeActionRepository::delete(['id' => $node_action_id])){
            $this->setError('删除失败！');
            Loggy::write('error','流程节点删除动作：删除动作失败！节点动作ID：'.$node_action_id);
            DB::rollBack();
            return false;
        }
        $this->setMessage('删除成功！');
        DB::commit();
        return true;
    }
}
            