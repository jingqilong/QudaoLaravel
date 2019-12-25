<?php


namespace App\ProcessStructure;


use App\Repositories\OaProcessActionsResultRepository;
use App\Repositories\OaProcessNodeActionRepository;
use App\Repositories\OaProcessNodeActionsResultRepository;
use App\Repositories\OaProcessNodeRepository;
use App\Repositories\OaProcessTransitionRepository;

class ActionResult
{
    public $id;

    public $node_actions_result_id;

    public $action_result_name;

    public $next_node = [];

    public function __construct($node_actions_result_id)
    {
        $this->node_actions_result_id = $node_actions_result_id;
    }

    /**
     *
     */
    public function setData(){
        if ($process_node_action = OaProcessNodeActionsResultRepository::getOne(['id' => $this->node_actions_result_id])){
            $action_result_id = $process_node_action['action_result_id'];
            $this->action_result_name = OaProcessActionsResultRepository::getField(
                ['id' => $action_result_id],
                'name'
            );
        }
    }

    /**
     * @desc 生成下一节点
     */
    public function buildNextNode(){
        if (!$transition = OaProcessTransitionRepository::getOne(['node_action_result_id' => $this->node_actions_result_id])){
            return;
        }
        if (empty($transition['next_node'])){
            return;
        }
        $nextNode = new Node($transition['next_node']);
        if ($current_node = OaProcessNodeRepository::getOne(['id' => $transition['current_node']])){
            #如果下一节点是当前节点之前，则不能继续创建
            if ($next_node = OaProcessNodeRepository::getOne(['id' => $transition['next_node']])){
                if ($next_node['position'] > $current_node['position']){
                    $nextNode->init();
                    $nextNode->setData();
                }
            }
        }
        $this->next_node = $nextNode;
    }
}