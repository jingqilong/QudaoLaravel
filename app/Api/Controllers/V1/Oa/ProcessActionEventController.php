<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Services\Oa\ProcessActionEventService;

class ProcessActionEventController extends ApiController
{
    protected $processActionEventService;

    /**
     * ProcessActionEventController constructor.
     * @param $processActionEventService
     */
    public function __construct(ProcessActionEventService $processActionEventService)
    {
        parent::__construct();
        $this->processActionEventService = $processActionEventService;
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/process_add_event",
     *     tags={"OA流程"},
     *     summary="流程添加事件",
     *     description="sang" ,
     *     operationId="process_add_event",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="node_id",
     *         in="query",
     *         description="节点ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="node_action_result_id",
     *         in="query",
     *         description="节点动作结果ID【注：事件类型为动作结果事件时，为必要参数】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="event_type",
     *         in="query",
     *         description="事件类型：（0：节点事件，1，动作结果事件）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="event_id",
     *         in="query",
     *         description="事件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="principals_type",
     *         in="query",
     *         description="相关人身份（1、执行人，2、监督人，3，发起人）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function processAddEvent(){
        $rules = [
            'node_id'               => 'required|integer',
            'node_action_result_id' => 'integer',
            'event_type'            => 'required|in:0,1',
            'event_id'              => 'required|integer',
            'principals_type'       => 'required|in:1,2,3',
        ];
        $messages = [
            'node_id.required'              => '节点ID不能为空！',
            'node_id.integer'               => '节点ID必须为整数！',
            'node_action_result_id.integer' => '节点动作结果ID必须为整数！',
            'event_type.required'           => '事件类型不能为空！',
            'event_type.in'                 => '事件类型不存在！',
            'event_id.required'             => '事件ID不能为空！',
            'event_id.integer'              => '事件ID必须为整数！',
            'principals_type.required'      => '相关人身份不能为空！',
            'principals_type.in'            => '相关人身份不存在！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processActionEventService->addActionEvent($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processActionEventService->message];
        }
        return ['code' => 100,'message' => $this->processActionEventService->error];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/oa/process/process_delete_event",
     *     tags={"OA流程"},
     *     summary="流程删除事件",
     *     description="sang" ,
     *     operationId="process_delete_event",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="node_action_event_id",
     *         in="query",
     *         description="节点动作事件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function processDeleteEvent(){
        $rules = [
            'node_action_event_id'  => 'required|integer',
        ];
        $messages = [
            'node_action_event_id.required' => '节点动作事件ID不能为空！',
            'node_action_event_id.integer'  => '节点动作事件ID必须为整数！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processActionEventService->deleteActionEvent($this->request['node_action_event_id']);
        if ($res){
            return ['code' => 200,'message' => $this->processActionEventService->message];
        }
        return ['code' => 100,'message' => $this->processActionEventService->error];
    }
    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/process_edit_event",
     *     tags={"OA流程"},
     *     summary="流程修改事件",
     *     description="sang" ,
     *     operationId="process_edit_event",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="node_action_event_id",
     *         in="query",
     *         description="节点动作事件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="event_id",
     *         in="query",
     *         description="事件ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="principals_type",
     *         in="query",
     *         description="相关人身份（1、执行人，2、监督人，3，发起人）",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="修改失败",),
     * )
     *
     */
    public function processEditEvent(){
        $rules = [
            'node_action_event_id'  => 'required|integer',
            'event_id'              => 'required|integer',
            'principals_type'       => 'required|in:1,2,3',
        ];
        $messages = [
            'node_action_event_id.required' => '节点动作事件ID不能为空！',
            'node_action_event_id.integer'  => '节点动作事件ID必须为整数！',
            'event_id.required'             => '事件ID不能为空！',
            'event_id.integer'              => '事件ID必须为整数！',
            'principals_type.required'      => '相关人身份不能为空！',
            'principals_type.in'            => '相关人身份不存在！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processActionEventService->editActionEvent($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processActionEventService->message];
        }
        return ['code' => 100,'message' => $this->processActionEventService->error];
    }
    /**
     * @OA\Get(
     *     path="/api/v1/oa/process/get_process_event_list",
     *     tags={"OA流程"},
     *     summary="获取流程事件列表",
     *     description="sang" ,
     *     operationId="get_process_event_list",
     *     @OA\Parameter(
     *         name="sign",
     *         in="query",
     *         description="签名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="node_id",
     *         in="query",
     *         description="节点ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="node_action_result_id",
     *         in="query",
     *         description="节点动作结果ID【此值不为空，则获取动作结果事件列表】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="页码",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page_num",
     *         in="query",
     *         description="每页显示条数",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function getProcessEventList(){
        $rules = [
            'node_id'               => 'required|integer',
            'node_action_result_id' => 'integer',
            'page'                  => 'integer',
            'page_num'              => 'integer',
        ];
        $messages = [
            'node_id.required'              => '节点ID不能为空！',
            'node_id.integer'               => '节点ID必须为整数！',
            'node_action_result_id.integer' => '节点动作结果ID必须为整数！',
            'page.integer'                  => '页码必须为整数',
            'page_num.integer'              => '每页显示条数必须为整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processActionEventService->getActionEventList($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processActionEventService->message,'data' => $res];
        }
        return ['code' => 100,'message' => $this->processActionEventService->error];
    }

}