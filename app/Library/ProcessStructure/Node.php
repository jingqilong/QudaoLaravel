<?php


namespace App\ProcessStructure;


use App\Repositories\OaProcessNodeActionRepository;
use App\Repositories\OaProcessNodeRepository;

class Node
{
    /**
     * 节点ID
     * @var Int
     */
    public $node_id;

    /**
     * 节点名称
     * @var string
     */
    public $name;

    /**
     * 节点步骤
     * @var int
     */
    public $position;

    /**
     * 节点说明
     * @var string
     */
    public $description;
    /**
     * 节点动作
     * @var array
     */
    public $node_actions = [];

    public $children = [];

    public $back_node_ids = [];

    /**
     * 需要返回的数据
     * @var array
     */

    /**
     * Node constructor.
     * @param Int $node_id
     */
    public function __construct(Int $node_id)
    {
        $this->node_id = $node_id;
    }

    /**
     * @desc 获取流程数据并填充
     */
    public function setData(){
        $id = $this->node_id;
        if ($process = OaProcessNodeRepository::getOne(['id' => $id])){
            $this->node_id      = $process['id'];
            $this->name         = $process['name'];
            $this->position     = $process['position'];
            $this->description  = $process['description'];
        }
        if ($node_actions = OaProcessNodeActionRepository::getList(['node_id' => $id])){
            foreach ($node_actions as $node_action){
                $action = new Action($node_action['action_id'],$node_action['id'],$this);
                $action->setData();
                $this->node_actions[] = $action;
            }
        }
    }

    /**
     * @param $node_id
     * @return bool
     */
    public function exists($node_id){
       foreach($this->children as $node ){
            if($node->exists($node_id)) {
                return true;
            }
            if($node_id == $this->node_id){
                return true;
            }
       }
       return false;
    }

    /**
     * 生成数据
     * @return array
     */
    public function buildData(){
        $return_data                = [];
        $return_data['node_id']     = $this->node_id;
        $return_data['name']        = $this->name;
        $return_data['position']    = $this->position;
        $return_data['description'] = $this->description;
        $return_data['back_node_ids'] = $this->back_node_ids;
        $return_data['node_actions']= [];
        foreach ($this->node_actions as $action){
            $return_data['node_actions'] = $action->buildData();
        }
        $return_data['children']    = [];
        foreach($this->children as $node){
            $return_data['children'][] = $node->buildData();
        }
        return $return_data;
    }
}