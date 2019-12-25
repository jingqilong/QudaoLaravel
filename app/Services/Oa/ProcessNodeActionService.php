<?php
namespace App\Services\Oa;


use App\Enums\ProcessActionEnum;
use App\Enums\ProcessEventEnum;
use App\Repositories\OaProcessActionPrincipalsRepository;
use App\Repositories\OaProcessActionRelatedRepository;
use App\Repositories\OaProcessActionsRepository;
use App\Repositories\OaProcessEventsRepository;
use App\Repositories\OaProcessNodeActionRepository;
use App\Repositories\OaProcessNodeRepository;
use App\Repositories\OaProcessTransitionDetailViewRepository;
use App\Repositories\OaProcessTransitionRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Tolawho\Loggy\Facades\Loggy;

class ProcessNodeActionService extends BaseService
{

    /**
     * 给节点添加动作
     * @param $node_id
     * @param $action_ids
     * @return bool
     */
    public function nodeAddAction($node_id, $action_ids)
    {
        /**
         * 添加步骤
         * 1，检查值有效性
         * 2，给节点添加动作
         * 3，给节点添加的动作添加相关记录
         */
        if (!OaProcessNodeRepository::exists(['id' => $node_id])){
            $this->setError('该节点不存在！');
            return false;
        }
        $action_ids = explode(',',$action_ids);
        $action_count = OaProcessActionsRepository::count(['id' => ['in', $action_ids],'status' => ProcessActionEnum::ENABLE]);
        if ($action_count != count($action_ids)){
            $this->setError('有无效动作！');
            return false;
        }
        DB::beginTransaction();
        foreach ($action_ids as $action_id){
            $action = OaProcessActionsRepository::getOne(['id' => $action_id]);
            if (OaProcessNodeActionRepository::exists(['node_id' => $node_id, 'action_id' => $action_id])){
                $this->setError('动作【'.$action['name'].'】已添加，请勿重复添加！');
                DB::rollBack();
                return false;
            }
            $add_node_action = [
                'node_id'       => $node_id,
                'action_id'     => $action_id,
                'created_at'    => time(),
                'updated_at'    => time(),
            ];
            if (!$node_action_id = OaProcessNodeActionRepository::getAddId($add_node_action)){
                $this->setError('添加失败！');
                DB::rollBack();
                return false;
            }
            if (empty($action['result'])){
                $this->setError('动作'.$action_id.'没有执行结果，无法添加！');
                DB::rollBack();
                return false;
            }
            $results = explode(',',$action['result']);
            foreach ($results as $result){
                $add_action_related = [
                    'node_action_id'    => $node_action_id,
                    'action_result'     => $result,
                    'created_at'        => time(),
                    'updated_at'        => time(),
                ];
                if (!OaProcessActionRelatedRepository::getAddId($add_action_related)){
                    $this->setError('添加失败！');
                    DB::rollBack();
                    return false;
                }
            }
        }
        DB::commit();
        $this->setMessage('添加成功！');
        return true;
    }

    /**
     * 给节点动作添加事件
     * @param $request
     * @return bool
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
     * @param integer $node_id      节点ID
     * @param integer $action_id    动作ID
     * @return bool
     */
    public function nodeDeleteAction($node_id, $action_id)
    {
        if (!OaProcessNodeRepository::exists(['id' => $node_id])){
            $this->setError('该节点不存在！');
            return false;
        }
        if (!OaProcessActionsRepository::exists(['id' => $action_id])){
            $this->setError('该动作不存在！');
            return false;
        }
        if (!$node_action = OaProcessNodeActionRepository::getOne(['node_id' => $node_id,'action_id' => $action_id])){
            $this->setError('该动作已被删除！');
            return false;
        }
        DB::beginTransaction();
        #删除动作下相关记录
        if ($action_related = OaProcessActionRelatedRepository::getOrderOne(['node_action_id' => $node_action['id']],'transition_id','desc')){
            if ($action_related['transition_id'] > 0){
                $this->setError('该动作结果已绑定下一节点，无法删除！');
                DB::rollBack();
                return false;
            }
            OaProcessActionRelatedRepository::delete(['node_action_id' => $node_action['id']]);
        }
        #删除动作下负责人记录
        if (!OaProcessActionPrincipalsRepository::delete(['node_action_id' => $node_action['id']])){
            Loggy::write('error','流程节点删除动作：删除节点动作负责人失败！节点动作ID：'.$node_action['id']);
        }
        #删除动作
        if (!OaProcessNodeActionRepository::delete(['node_id' => $node_id,'action_id' => $action_id])){
            $this->setError('删除失败！');
            DB::rollBack();
            return false;
        }
        $this->setMessage('删除成功！');
        DB::commit();
        return true;
    }


}
            