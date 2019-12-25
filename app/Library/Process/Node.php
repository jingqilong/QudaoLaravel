<?php


namespace App\Process;


class Node
{
    public $name;
    public $id;
    public $position;

    public $actions = [];
    public $children = [];
    public $parent;
    public $transitions = [];
    public $return_data;

    public function __construct(Int $id, Node $parent=Null)
    {
        $this->id = $id;
        if(null!==$parent){
            $this->parent = $parent;
        }
    }

    public function init(){
        foreach($this->transitions as $transition){
            $child  = new Node($transition['next_node'],$this);
            $child->init();
        }
    }

    public function buildData(){
        $return_data = [];
        foreach($this->children as $node){
            $return_data['children'][] = $node->buildData();
        }
        $return_data['data']['name'] = $this->name;
        $return_data['data']['id'] = $this->id;
        $return_data['data']['position'] = $this->position;
        return $return_data;
    }

}