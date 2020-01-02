<?php


namespace App\ProcessStructure;


use App\Enums\ProcessTransitionStatusEnum;
use App\Repositories\OaProcessActionsResultRepository;
use App\Repositories\OaProcessNodeRepository;
use App\Repositories\OaProcessTransitionRepository;
use Tolawho\Loggy\Facades\Loggy;

class ActionResult
{
    /**
     * 动作结果ID
     * @var int
     */
    public $action_result_id = 0;

    /**
     * 节点动作结果ID
     * @var int
     */
    public $node_actions_result_id = 0;

    /**
     * 动作结果名称
     * @var string
     */
    public $action_result_name = '';

    /**
     * 流转ID
     * @var int
     */
    public $transition_id = 0;

    /**
     * 流转状态标签
     * @var string
     */
    public $transition_status_label = '';

    /**
     * 流转状态
     * @var int
     */
    public $transition_status = 0;

    /**
     * 下一节点
     * @var array
     */
    public $next_node_id = 0;
    /**
     * 下一节点名称
     * @var array
     */
    public $next_node_name = '';

    public $parent_node;


    /**
     * ActionResult constructor.
     * @param int $node_actions_result_id
     * @param int $id
     * @param Action $parent_node
     */
    public function __construct(int $node_actions_result_id,int $id,Action $parent_node)
    {
        $this->node_actions_result_id   = $node_actions_result_id;
        $this->action_result_id         = $id;
        $this->parent_node              = $parent_node;
    }

    /**
     * @desc 设置数据
     */
    public function setData(){
        $this->action_result_name = OaProcessActionsResultRepository::getField(
            ['id' => $this->action_result_id],
            'name'
        );
    }

    /**
     * @desc 生成下一节点
     */
    public function buildNextNode(){
        $parent_node = $this->getParent();
        if (!$transition = OaProcessTransitionRepository::getOne(['node_action_result_id' => $this->node_actions_result_id])){
            return;
        }
        $this->transition_id            = $transition['id'];
        $this->transition_status        = $transition['status'];
        $this->transition_status_label  = ProcessTransitionStatusEnum::getLabelByValue($transition['status']);
        if (empty($transition['next_node']) || $transition['current_node'] == $transition['next_node']){
            return;
        }
        if (OaProcessNodeRepository::exists(['id' => $transition['current_node']])){
            $this->next_node_id = $transition['next_node'];
            #如果下一节点已经创建，则不能继续创建
            if($this->exists($transition['next_node'])){
                if (!in_array($transition['next_node'],$parent_node->back_node_ids))
                $parent_node->back_node_ids[] = $transition['next_node'];
            }else{
                $nextNode = new Node($transition['next_node']);
                $nextNode->setData();
                if ($nextNode->position > $parent_node->position){
                    $nextNode->buildAction();
                    $nextNode->is_parent = true;
                }
                $parent_node->children[] = $nextNode;
            }
            if ($next_node = OaProcessNodeRepository::getOne(['id' => $transition['next_node']])){
                $this->next_node_name  = $next_node['name'];
            }
        }
    }

    /**
     * @return mixed
     */
    public function getParent(){
        $parent_node = $this->parent_node;
        return $parent_node->getParent();
    }

    /**
     * @param $node_id
     * @return mixed
     */
    public function exists($node_id){
        $action = $this->parent_node;
        return $action->exists($node_id);
    }

    /**
     * 生成数据
     * @return array
     */
    public function buildData(){
        $parent_node_id                         = $this->getParent()->node_id;
        $return_data['action_result_id']        = $this->action_result_id;
        $return_data['node_actions_result_id']  = $this->node_actions_result_id;
        $return_data['action_result_name']      = $this->action_result_name;
        $return_data['transition_id']           = $this->transition_id;
        $return_data['transition_status_label'] = $this->transition_status_label;
        $return_data['transition_status']       = $this->transition_status;
        $return_data['parent_node_id']          = $parent_node_id;
        $return_data['next_node_id']            = $this->next_node_id;
        $return_data['next_node_name']          = $this->next_node_name;
        return $return_data;
    }
}