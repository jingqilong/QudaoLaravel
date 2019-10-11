<?php
namespace App\Services\Oa;


use App\Enums\ProcessDefinitionEnum;
use App\Enums\ProcessPrincipalsEnum;
use App\Repositories\OaEmployeeRepository;
use App\Repositories\OaProcessActionPrincipalsRepository;
use App\Repositories\OaProcessActionRelatedRepository;
use App\Repositories\OaProcessActionsRepository;
use App\Repositories\OaProcessCategoriesRepository;
use App\Repositories\OaProcessDefinitionRepository;
use App\Repositories\OaProcessEventsRepository;
use App\Repositories\OaProcessNodeActionRepository;
use App\Repositories\OaProcessNodeRepository;
use App\Repositories\OaProcessTransitionRepository;
use App\Services\BaseService;

class ProcessDefinitionService extends BaseService
{

    /**
     * 创建一个流程
     * @param $request
     * @return bool
     */
    public function createProcess($request)
    {
        if (!OaProcessCategoriesRepository::exists(['id' => $request['category_id']])){
            $this->setError('流程分类不存在！');
            return false;
        }
        if (OaProcessDefinitionRepository::exists(['name' => $request['name']])){
            $this->setError('名称已被使用！');
            return false;
        }
        $add_arr = [
            'name'          => $request['name'],
            'category_id'   => $request['category_id'],
            'description'   => $request['description'] ?? '',
            'status'        => ProcessDefinitionEnum::getConst($request['status']),
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (OaProcessDefinitionRepository::getAddId($add_arr)){
            $this->setMessage('创建成功！');
            return true;
        }
        $this->setError('创建失败！');
        return false;
    }

    /**
     * 删除流程
     * @param $id
     * @return bool
     */
    public function deleteProcess($id)
    {
        if (!OaProcessDefinitionRepository::exists(['id' => $id])){
            $this->setError('该流程已被删除!');
            return false;
        }
        if (OaProcessDefinitionRepository::delete(['id' => $id])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }

    /**
     * 修改流程定义
     * @param $request
     * @return bool
     */
    public function editProcess($request)
    {
        if (!$definition = OaProcessDefinitionRepository::getOne(['id' => $request['id']])){
            $this->setError('该流程不存在！');
            return false;
        }
        if (!OaProcessCategoriesRepository::exists(['id' => $request['category_id']])){
            $this->setError('流程分类不存在！');
            return false;
        }
        if (OaProcessDefinitionRepository::exists(['name' => $request['name']]) && $definition['name'] != $request['name']){
            $this->setError('该名称已被使用！');
            return false;
        }
        $upd_arr = [
            'name'          => $request['name'],
            'category_id'   => $request['category_id'],
            'description'   => $request['description'] ?? '',
            'status'        => ProcessDefinitionEnum::getConst($request['status']),
            'updated_at'    => time(),
        ];
        if (OaProcessDefinitionRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取流程列表
     * @param $page
     * @param $pageNum
     * @return mixed
     */
    public function getProcessList($page, $pageNum)
    {
        if (!$definition_list = OaProcessDefinitionRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$pageNum)){
            $this->setError('获取失败!');
            return false;
        }
        unset($definition_list['first_page_url'], $definition_list['from'],
            $definition_list['from'], $definition_list['last_page_url'],
            $definition_list['next_page_url'], $definition_list['path'],
            $definition_list['prev_page_url'], $definition_list['to']);
        if (empty($definition_list['data'])){
            $this->setMessage('暂无数据!');
            return $definition_list;
        }
        foreach ($definition_list['data'] as &$value){
            $value['status_label'] = ProcessDefinitionEnum::getStatus($value['status']);
            $value['created_at'] = date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at'] = date('Y-m-d H:m:s',$value['updated_at']);
        }
//        $definition_list = [
//            '流程ID',
//            '流程名称',
//            '流程启用状态',
//            '流程分类',
//            '流程描述',
//            '流程总步骤数',
//            '创建时间',
//            '更新时间',
//            '第一步节点' => [
//                '节点ID',
//                '节点名称',
//                '完成时间',
//                '节点图标',
//                '步骤位置',
//                '节点描述',
//                '节点动作' => [
//                    '动作1' => [
//                        '节点动作ID',
//                        '动作ID',
//                        '动作名称',
//                        '动作负责人' => [
//                            '执行人' => [
//                                '执行人ID',
//                                '执行人名称'
//                            ],
//                            '监督人' => [
//                                '监督人ID',
//                                '监督人名称'
//                            ],
//                            '代理人' => [
//                                '代理人ID',
//                                '代理人名称'
//                            ],
//                        ],
//                        '动作执行结果' => [
//                            '结果1' => [
//                                '事件' => [
//                                    '事件1' => [
//                                        '事件名称' => '发短信',
//                                        '事件描述',
//                                    ],
//                                    '事件2' => [
//                                        '事件名称' => '发邮件',
//                                        '事件描述',
//                                    ],
//                                ],
//                                '结果状态' => '跳转下一节点',
//                                '第二步节点' => ['...']
//                            ],
//                            '结果2' => [
//                                '事件' => [
//                                    '事件1' => [
//                                        '事件名称' => '发短信',
//                                        '事件描述',
//                                    ],
//                                    '事件2' => [
//                                        '事件名称' => '发邮件',
//                                        '事件描述',
//                                    ],
//                                ],
//                                '结果状态' => '回跳到某个节点',
//                                '第二部节点' => '回跳节点ID'
//                            ],
//                            '结果3' => [
//                                '事件' => [],
//                                '结果状态' => '流程结束',
//                                '结束' => '0'
//                            ],
//                        ],
//                    ]
//                ]
//            ]
//        ];
        $this->setMessage('获取成功！');
        return $definition_list;
    }
//TODO 到这儿了

    /**
     * 获取流程详情
     * @param $process_id
     * @return mixed
     */
    public function getProcessDetail($process_id){
        if (!$process = OaProcessDefinitionRepository::getOne(['id' => $process_id])){
            $this->setError('该流程不存在！');
        }
        $process['status_label'] = ProcessDefinitionEnum::getStatus($process['status']);
        $process['created_at']   = date('Y-m-d H:m:s',$process['created_at']);
        $process['updated_at']   = date('Y-m-d H:m:s',$process['updated_at']);
        $process['node']         = [];
        #流程子数据初始化
        $node_action_list       = [];   //所有节点动作列表
        $action_principals_list = [];   //所有节点动作负责人关联列表
        $principal_list         = [];   //所有负责人列表
        $action_related_list    = [];   //所有节点动作相关列表
        $action_list            = [];   //所有节点动作相关动作列表
        $event_list             = [];   //所有节点动作相关事件列表
        $transition_list        = [];   //所有节点流转列表
        #获取所有流程子数据
        if ($node_list = OaProcessNodeRepository::getList(['process_id' => $process_id],['*'],'position','asc')){
            $node_ids = array_column($node_list,'id');
            if ($node_action_list = OaProcessNodeActionRepository::getList(['node_id' => ['in', $node_ids]])){
                $node_action_ids = array_column($node_action_list,'id');
                if ($action_principals_list = OaProcessActionPrincipalsRepository::getList(['node_action_id' => ['in', $node_action_ids]])){
                    $principal_ids = array_column($action_principals_list,'principal_id');
                    $principal_list = OaEmployeeRepository::getList(['id' => ['in', $principal_ids]],['username','real_name']);
                }
                if ($action_related_list = OaProcessActionRelatedRepository::getList(['node_action_id' => ['in', $node_action_ids]])){
                    $event_ids = array_column($action_related_list,'event_ids');
                    $event_ids = implode(',',$event_ids);
                    $event_ids = explode(',',$event_ids);
                    $event_ids = array_unique($event_ids);
                    $event_list = OaProcessEventsRepository::getList(['id' => ['in', $event_ids]],['name','description']);
                    $transition_ids = array_column($action_related_list,'transition_id');
                    $transition_list = OaProcessTransitionRepository::getList(['id' => ['in', $transition_ids]]);
                }
                $action_ids = array_column($action_related_list,'action_id');
                $action_list = OaProcessActionsRepository::getList(['id' => ['in', $action_ids]],['name','description']);
            }
        }

        if ($nodes = $this->searchArray($node_list,'process_id',$process_id)){
            foreach ($nodes as &$node){
                $node['actions'] = [];
                if ($node_actions = $this->searchArray($node_action_list,'node_id',$node['id'])){
                    foreach ($node_actions as &$node_action){
                        $principals = [];
                        if ($action_principals = $this->searchArray($action_principals_list,'node_action_id',$node_action['id'])){
                            foreach ($action_principals as $action_principal){
                                $principals[] = $this->searchArray($principal_list,'id',$action_principal['principal_id']) +
                                    ['principal_iden' => ProcessPrincipalsEnum::getStatus($action_principal['principal_iden'])];
                            }
                        }
                        $events = [];
                        $next_node_id = 0;
                        if ($action_relates = $this->searchArray($action_related_list,'node_action_id',$node_action['id'])){
                            foreach ($action_relates as $action_relate){
                                if ($action_relate['event_ids']){
                                    $event_ids = explode(',',$action_relate['event_ids']);
                                    foreach ($event_ids as $id){
                                        $events[] = $this->searchArray($event_list,'id',$id);
                                    }
                                }
                                if ($action_relate['transition_id']){
                                    $next_node = $this->searchArray($transition_list,'id',$action_relate['transition_id']);
                                    $next_node_id = $next_node['next_node'];
                                }
                            }
                        }
                        $node_action['principals'] = $principals;
                        $node_action['next_node_id'] = $next_node_id;
                    }
                    if ($actions = $this->searchArray($action_list,'id',$node_actions['action_id'])){
                        $node_actions += $actions;
                    }
                    $node['actions'] = $node_actions;
                }
            }
            $process['nodes'] = $nodes;
        }
        $this->setMessage('获取成功！');
        return $process;
    }

    /**
     * 数组中查询指定键值的数据
     * @param $array
     * @param $filed
     * @param $value
     * @return array|bool
     */
    function searchArray($array, $filed, $value){
        if (!in_array($value,array_column($array,$filed))){
            return false;
        }
        $res = [];
        foreach ($array as $k => $v){
            if (is_array($v) && $v[$filed] == $value){
                $res[] = $v;continue;
            }
            if ($k == $filed && $v == $value){
                $res = $array;break;
            }
        }
        return $res;
    }

    /**
     * 检查数组中是否存在指定键对应值
     * @param $array
     * @param $filed
     * @param $value
     * @return bool
     */
    function existsArray($array, $filed, $value){
        if (in_array($value,array_column($array,$filed))){
            return true;
        }
        return false;
    }
}
            