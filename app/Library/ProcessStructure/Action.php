<?php


namespace App\ProcessStructure;


use App\Repositories\OaProcessActionsRepository;
use App\Repositories\OaProcessNodeActionsResultRepository;

class Action
{
    /**
     * @var int
     */
    public $id;

    public $name;

    public $status;

    public $description;

    /**
     * @var int
     */
    public $node_action_id = 0;

    public $node_action_results = [];

    public function __construct($id,$node_action_id)
    {
        $this->id = $id;
        $this->node_action_id = $node_action_id;
    }

    /**
     * @desc 写入节点动作数据
     */
    public function setData(){
        if ($action = OaProcessActionsRepository::getOne(['id' => $this->id])){
            $this->id           = $action['id'];
            $this->name         = $action['name'];
            $this->status       = $action['status'];
            $this->description  = $action['description'];
        }
    }

    /**
     * @desc
     */
    public function buildNodeActionResult(){
        if ($node_action_results = OaProcessNodeActionsResultRepository::getList(['node_action_id' => $this->node_action_id])){
            foreach ($node_action_results as $node_action_result){
                $action_result = new ActionResult($node_action_result['action_result_id']);
                $action_result->setData();
                $action_result->buildNextNode();
                $node_action_results[] = $action_result;
            }
        }
    }
}