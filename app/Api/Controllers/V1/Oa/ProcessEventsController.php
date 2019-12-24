<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Services\Oa\ProcessEventsService;

class ProcessEventsController extends ApiController
{
    protected $processEventsService;

    /**
     * ProcessEventsController constructor.
     * @param $processEventsService
     */
    public function __construct(ProcessEventsService $processEventsService)
    {
        parent::__construct();
        $this->processEventsService = $processEventsService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/add_event",
     *     tags={"OA流程"},
     *     summary="添加事件",
     *     description="sang" ,
     *     operationId="add_event",
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
     *          name="name",
     *          in="query",
     *          description="事件名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="event_type",
     *          in="query",
     *          description="事件类型，1钉邮、2短信、3站内信、4微信推送",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="事件状态（1:启用，2:禁用）",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="description",
     *          in="query",
     *          description="事件描述",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function addEvent(){
        $rules = [
            'name'          => 'required',
            'event_type'    => 'required|in:1,2,3,4',
            'status'        => 'required|in:1,2',
        ];
        $messages = [
            'name.required'         => '事件名称不能为空！',
            'event_type.required'   => '事件类型不能为空！',
            'event_type.in'         => '事件类型不存在！',
            'status.required'       => '事件状态不能为空！',
            'status.in'             => '事件状态值有误！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processEventsService->addEvent($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processEventsService->message];
        }
        return ['code' => 100,'message' => $this->processEventsService->error];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/oa/process/delete_event",
     *     tags={"OA流程"},
     *     summary="删除事件",
     *     description="sang" ,
     *     operationId="delete_event",
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
     *          name="event_id",
     *          in="query",
     *          description="事件ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function deleteEvent(){
        $rules = [
            'event_id'  => 'required|integer'
        ];
        $messages = [
            'event_id.required'       => '事件ID不能为空！',
            'event_id.integer'        => '事件ID必须为整数！'
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processEventsService->deleteEvent($this->request['event_id']);
        if ($res){
            return ['code' => 200,'message' => $this->processEventsService->message];
        }
        return ['code' => 100,'message' => $this->processEventsService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/edit_event",
     *     tags={"OA流程"},
     *     summary="修改事件",
     *     description="sang" ,
     *     operationId="edit_event",
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
     *          name="event_id",
     *          in="query",
     *          description="事件ID",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="事件名称",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="event_type",
     *          in="query",
     *          description="事件类型，1钉邮、2短信、3站内信、4微信推送",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="事件状态（1:启用，2:禁用）",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="description",
     *          in="query",
     *          description="事件描述",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function editEvent(){
        $rules = [
            'event_id'      => 'required|integer',
            'name'          => 'required',
            'event_type'    => 'required|in:L1,2,3,4',
            'status'        => 'required|in:1,2',
        ];
        $messages = [
            'event_id.required'     => '事件ID不能为空！',
            'event_id.integer'      => '事件ID必须为整数！',
            'name.required'         => '事件名称不能为空！',
            'event_type.required'   => '事件类型不能为空！',
            'event_type.in'         => '事件类型不存在！',
            'status.required'       => '事件状态不能为空！',
            'status.in'             => '事件状态值有误！',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processEventsService->editEvent($this->request);
        if ($res){
            return ['code' => 200,'message' => $this->processEventsService->message];
        }
        return ['code' => 100,'message' => $this->processEventsService->error];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/oa/process/get_event_list",
     *     tags={"OA流程"},
     *     summary="获取事件列表",
     *     description="sang" ,
     *     operationId="get_event_list",
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
     *         name="page",
     *         in="query",
     *         description="页码",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page_num",
     *         in="query",
     *         description="每页显示条数",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function getEventList(){
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processEventsService->getEventList(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if ($res === false){
            return ['code' => 100,'message' => $this->processEventsService->error];
        }
        return ['code' => 200,'message' => $this->processEventsService->message, 'data' => $res];
    }

}