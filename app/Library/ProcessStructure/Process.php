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
    public $process_id;
    /**
     * 流程名称
     * @var string
     */
    public $name = '';
    /**
     * 流程描述
     * @var string
     */
    public $description = '';


    /**
     * @var Node
     */
    public $node;

    public function __construct($id)
    {
        $this->process_id = $id;
    }

    /**
     * @desc 获取流程数据并填充
     */
    public function setData(){
        $id = $this->process_id;
        if ($process = OaProcessDefinitionRepository::getOne(['id' => $id])){
            $this->process_id   = $process['id'];
            $this->name         = $process['name'];
            $this->description  = $process['description'];
        }
        if ($first_node = OaProcessNodeRepository::getOrderOne(['process_id' => $id],'position','asc')){
            $first_node_id = $first_node['id'];
            $node = new Node($first_node_id);
            $node->setData();
            $this->node = $node;
        }
    }

    /**
     * 生成数据
     * @return array
     */
    public function buildData(){
        $return_data['process_id']  = $this->process_id;
        $return_data['name']        = $this->name;
        $return_data['description'] = $this->description;
        $return_data['children']  = [];
        if ($this->node){
            $return_data['children'][]= $this->node->buildData();//原名node
        }
        return $return_data;
    }
}