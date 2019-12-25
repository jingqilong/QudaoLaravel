<?php


namespace App\ProcessStructure;


use App\Repositories\OaProcessNodeActionRepository;
use App\Repositories\OaProcessNodeRepository;
use App\Repositories\OaProcessTransitionRepository;

class Node
{
    /**
     * @var Int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $position;

    /**
     * @var string
     */
    public $description;

    /**
     * 动作
     * @var array
     */
    public $actions = [];

    /**
     * 子节点
     * @var array
     */
    public $children = [];

    /**
     * 父级节点
     * @var Node|Null
     */
    public $parent;

    /**
     * 流转
     * @var array
     */
    public $transitions = [];

    /**
     * 节点动作
     * @var array
     */
    public $node_actions = [];

    /**
     * 需要返回的数据
     * @var array
     */
    public $return_data;

    /**
     * Node constructor.
     * @param Int $id
     * @param Node|Null $parent
     */
    public function __construct(Int $id, Node $parent=Null)
    {
        $this->id = $id;
        if(null!==$parent){
            $this->parent = $parent;
        }
    }

    /**
     *
     */
    public function init(){
        foreach($this->transitions as $transition){
            if ($current_node = OaProcessNodeRepository::getOne(['id' => $transition['current_node']])){
                #如果下一节点是当前节点之前，则不能继续创建
                if ($next_node = OaProcessNodeRepository::getOne(['id' => $transition['next_node']])){
                    if ($next_node['position'] <= $current_node['position']){
                        continue;
                    }
                }
            }
            $child  = new Node($transition['next_node'],$this);
            $child->init();
            $this->children[] = $child;
        }
    }

    /**
     * 生成数据
     * @return array
     */
    public function buildData(){
        $return_data = [];
        foreach($this->children as $node){
            $return_data['children'][] = $node->buildData();
        }
        $return_data['data']['id']          = $this->id;
        $return_data['data']['name']        = $this->name;
        $return_data['data']['position']    = $this->position;
        $return_data['data']['description'] = $this->description;
        $this->return_data = $return_data;
        return $return_data;
    }

    /**
     * @desc 获取流程数据并填充
     */
    public function setData(){
        $id = $this->id;
        if ($process = OaProcessNodeRepository::getOne(['id' => $id])){
            $this->id           = $process['id'];
            $this->name         = $process['name'];
            $this->position     = $process['position'];
            $this->description  = $process['description'];
        }
        if ($transitions = OaProcessTransitionRepository::getList(['current_node' => $id])){
            $this->transitions = $transitions;
        }
        if ($node_actions = OaProcessNodeActionRepository::getList(['node_id' => $id])){
            foreach ($node_actions as $node_action){
                $action = new Action($node_action['action_id'],$node_action['id']);
                $action->setData();
                $action->buildNodeActionResult();
                $this->node_actions[] = $action;
            }
        }
    }
}