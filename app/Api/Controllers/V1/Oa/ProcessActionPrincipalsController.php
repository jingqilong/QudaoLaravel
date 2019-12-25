<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Enums\ProcessPrincipalsEnum;
use App\Services\Oa\ProcessActionPrincipalsService;

class ProcessActionPrincipalsController extends ApiController
{
    public $processActionPrincipalsService;

    /**
     * ProcessActionPrincipalsController constructor.
     * @param $processActionPrincipalsService
     */
    public function __construct(ProcessActionPrincipalsService $processActionPrincipalsService)
    {
        parent::__construct();
        $this->processActionPrincipalsService = $processActionPrincipalsService;
    }



    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/add_node_action_principal",
     *     tags={"OA流程"},
     *     summary="添加节点动作负责人",
     *     description="sang" ,
     *     operationId="add_node_action_principal",
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
     *         name="node_action_id",
     *         in="query",
     *         description="节点动作ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="principal_id",
     *         in="query",
     *         description="负责人ID，如果是添加发起人，此参数为0",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="principal_iden",
     *         in="query",
     *         description="负责人身份（1:执行人，2:监督人，3发起人，4:代理人）",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加失败！",),
     * )
     *
     */
    public function addNodeActionPrincipal(){
        $rules = [
            'node_action_id'   => 'required|integer',
            'principal_iden'   => 'required|in:'.ProcessPrincipalsEnum::getPrincipalString(),
            'principal_id'     => 'required|integer',
        ];
        $messages = [
            'node_action_id.required'   => '节点动作ID不能为空！',
            'node_action_id.integer'    => '节点动作ID必须为整数！',
            'principal_iden.required'   => '负责人身份不能为空！',
            'principal_iden.in'         => '负责人身份有误！',
            'principal_id.required'     => '负责人ID不能为空！',
            'principal_id.integer'      => '负责人ID必须为整数！',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processActionPrincipalsService->createPrincipal($this->request['node_action_id'],$this->request['principal_iden'],$this->request['principal_id']);
        if ($res){
            return ['code' => 200,'message' => $this->processActionPrincipalsService->message];
        }
        return ['code' => 100,'message' => $this->processActionPrincipalsService->error];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/oa/process/delete_node_action_principal",
     *     tags={"OA流程"},
     *     summary="删除节点动作负责人",
     *     description="sang" ,
     *     operationId="delete_node_action_principal",
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
     *         name="node_action_principal_id",
     *         in="query",
     *         description="节点动作负责人ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="删除失败！",),
     * )
     *
     */
    public function deleteNodeActionPrincipal(){
        $rules = [
            'node_action_principal_id'   => 'required|integer',
        ];
        $messages = [
            'node_action_principal_id.required'   => '节点动作负责人ID不能为空！',
            'node_action_principal_id.integer'    => '节点动作负责人ID必须为整数！',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processActionPrincipalsService->deletePrincipal($this->request['node_action_principal_id']);
        if ($res){
            return ['code' => 200,'message' => $this->processActionPrincipalsService->message];
        }
        return ['code' => 100,'message' => $this->processActionPrincipalsService->error];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/oa/process/edit_node_action_principal",
     *     tags={"OA流程"},
     *     summary="修改节点动作负责人",
     *     description="sang" ,
     *     operationId="edit_node_action_principal",
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
     *         name="node_action_principal_id",
     *         in="query",
     *         description="节点动作负责人ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="node_action_id",
     *         in="query",
     *         description="节点动作ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="principal_id",
     *         in="query",
     *         description="负责人ID，如果是添加发起人，此参数为0",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="principal_iden",
     *         in="query",
     *         description="负责人身份（1:执行人，2:监督人，3发起人，4:代理人）",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加失败！",),
     * )
     *
     */
    public function editNodeActionPrincipal(){
        $rules = [
            'node_action_principal_id'  => 'required|integer',
            'node_action_id'            => 'required|integer',
            'principal_iden'            => 'required|in:'.ProcessPrincipalsEnum::getPrincipalString(),
            'principal_id'              => 'required|integer',
        ];
        $messages = [
            'node_action_principal_id.required' => '节点动作负责人ID不能为空！',
            'node_action_principal_id.integer'  => '节点动作负责人ID必须为整数！',
            'node_action_id.required'           => '节点动作ID不能为空！',
            'node_action_id.integer'            => '节点动作ID必须为整数！',
            'principal_iden.required'           => '负责人身份不能为空！',
            'principal_iden.in'                 => '负责人身份有误！',
            'principal_id.required'             => '负责人ID不能为空！',
            'principal_id.integer'              => '负责人ID必须为整数！',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processActionPrincipalsService->updatePrincipal(
            $this->request['node_action_principal_id'],$this->request['node_action_id'],$this->request['principal_iden'],$this->request['principal_id']);
        if ($res){
            return ['code' => 200,'message' => $this->processActionPrincipalsService->message];
        }
        return ['code' => 100,'message' => $this->processActionPrincipalsService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/process/get_node_action_principal_list",
     *     tags={"OA流程"},
     *     summary="获取节点动作负责人列表",
     *     description="sang" ,
     *     operationId="get_node_action_principal_list",
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
     *         name="node_action_id",
     *         in="query",
     *         description="节点动作ID",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="principal_iden",
     *         in="query",
     *         description="负责人身份（1:执行人，2:监督人，3发起人，4:代理人）",
     *         required=false,
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
     *     @OA\Response(response=100,description="添加失败！",),
     * )
     *
     */
    public function getNodeActionPrincipalList(){
        $rules = [
            'node_action_id'            => 'integer',
            'principal_iden'            => 'in:'.ProcessPrincipalsEnum::getPrincipalString(),
            'page'                      => 'integer',
            'page_num'                  => 'integer',
        ];
        $messages = [
            'node_action_id.integer'            => '节点动作ID必须为整数！',
            'principal_iden.in'                 => '负责人身份有误！',
            'page.integer'                      => '页码必须为整数',
            'page_num.integer'                  => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->processActionPrincipalsService->getPrincipalList(
            $this->request['node_action_id'] ?? null,
            $this->request['principal_iden'] ?? null,
            $this->request['page'] ?? 1,
            $this->request['page_num'] ?? 20
        );
        if ($res){
            return ['code' => 200,'message' => $this->processActionPrincipalsService->message,'data' => $res];
        }
        return ['code' => 100,'message' => $this->processActionPrincipalsService->error];
    }
}