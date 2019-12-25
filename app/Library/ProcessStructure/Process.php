<?php


namespace App\ProcessStructure;


use App\Repositories\OaProcessDefinitionRepository;
use App\Repositories\OaProcessNodeRepository;

class Process
{
    /**
     * 流程ID
     * @var int
     */
    public $id;
    /**
     * 流程名称
     * @var string
     */
    public $name;
    /**
     * 流程描述
     * @var string
     */
    public $description;

    /**
     * 第一个节点
     * @var int
     */
    public $first_node_id = 0;

    /**
     * @var Node
     */
    public $node;

    public $node_data;

    public function __construct($id)
    {
        $this->setData($id);
    }

    /**
     * @desc 获取流程数据并填充
     * @param $id
     */
    public function setData($id){
        if ($process = OaProcessDefinitionRepository::getOne(['id' => $id])){
            $this->id           = $process['id'];
            $this->name         = $process['name'];
            $this->description  = $process['description'];
        }
        if ($first_node = OaProcessNodeRepository::getOrderOne(['process_id' => $this->id],'position','asc')){
            $this->first_node_id = $first_node['id'];
        }
    }

    /**
     * @desc 获取流程节点
     */
    public function getNode(){
        $node = new Node($this->first_node_id);
        $node->setData();
//        $node->init();
        $this->node = $node;
    }

    /**
     * 获取节点数据
     * @return array
     */
    public function getNodeData(){
        return $this->node->buildData();
    }
}