<?php


namespace App\ProcessStructure;


use App\Repositories\OaProcessActionsRepository;
use App\Repositories\OaProcessNodeActionsResultRepository;

class Action
{
    /**
     * 动作ID
     * @var int
     */
    public $action_id = 0;

    /**动作名称
     * @var string
     */
    public $name = '';
    /**动作状态
     * @var int
     */
    public $status = 0;
    /**动作说明
     * @var string
     */
    public $description = '';

    /**
     * 节点动作ID
     * @var int
     */
    public $node_action_id = 0;
    /**
     * 所有节点动作结果
     * @var array
     */
    public $node_action_results = [];

    /**
     * @var Node
     */
    public $parent_node;

    /**
     * Action constructor.
     * @param $id
     * @param $node_action_id
     * @param $parent_node
     */
    public function __construct($id,$node_action_id,Node $parent_node)
    {
        $this->action_id        = $id;
        $this->node_action_id   = $node_action_id;
        $this->parent_node      = $parent_node;
    }

    /**
     * @desc 写入节点动作数据
     */
    public function setData(){
        if ($action = OaProcessActionsRepository::getOne(['id' => $this->action_id])){
            $this->action_id    = $action['id'];
            $this->name         = $action['name'];
            $this->status       = $action['status'];
            $this->description  = $action['description'];
        }
        if ($node_action_results = OaProcessNodeActionsResultRepository::getList(['node_action_id' => $this->node_action_id])){
            foreach ($node_action_results as $node_action_result){
                $action_result = new ActionResult($node_action_result['id'],$node_action_result['action_result_id'],$this);
                $action_result->setData();
                $action_result->buildNextNode();
                $this->node_action_results[] = $action_result;
            }
        }
    }

    /**
     * @return Node
     */
    public function getParent(){
        return $this->parent_node;
    }

    /**
     * @param $node_id
     * @return bool
     */
    public function exists($node_id){
        $node = $this->parent_node;
        return $node->exists($node_id);
    }

    /**
     * 生成数据
     * @return array
     */
    public function buildData(){
        $return_data = [];
        $return_data['action_id']           = $this->action_id;
        $return_data['name']                = $this->name;
        $return_data['status']              = $this->status;
        $return_data['description']         = $this->description;
        $return_data['node_action_id']      = $this->node_action_id;
        foreach($this->node_action_results as $action_result){
            $action_result_data = $action_result->buildData();
            $return_data['node_action_results'][] = $action_result_data;
        }
        return $return_data;
    }
}