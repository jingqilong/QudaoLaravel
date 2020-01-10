<?php


namespace App\Api\Controllers\V1\Member;


use App\Api\Controllers\ApiController;
use App\Services\Member\MemberContactServices;

class MemberContactController extends ApiController
{
    public $memberContactServices;
    /**
     * @var MemberContactServices

    /**
     * MemberContactController constructor.
     * @param MemberContactServices
     */
    public function __construct(MemberContactServices $memberContactServices)
    {
        parent::__construct();
        $this->memberContactServices = $memberContactServices;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/member/add_member_contact",
     *     tags={"成员联系申请"},
     *     summary="添加成员查看成员联系请求",
     *     operationId="add_member_contact",
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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="contact_id",
     *         in="query",
     *         description="需求联系人id",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="needs_value",
     *         in="query",
     *         description="联系需求说明",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function addMemberContact(){
        $rules = [
            'contact_id'       => 'required|integer',
            'needs_value'      => 'required',
        ];
        $messages = [
            'contact_id.required'    => '需求联系人id不能为空',
            'contact_id.integer'     => '需求联系人id不是整数',
            'needs_value.required'   => '需求内容不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberContactServices->addMemberContact($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->memberContactServices->error];
        }
        return ['code' => 200, 'message' => $this->memberContactServices->message];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/member/edit_member_contact",
     *     tags={"成员联系申请"},
     *     summary="修改成员查看成员的联系请求",
     *     operationId="edit_member_contact",
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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="id",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="contact_id",
     *         in="query",
     *         description="需求联系人id",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="needs_value",
     *         in="query",
     *         description="联系需求说明",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function editMemberContact(){
        $rules = [
            'id'            => 'required|integer',
            'contact_id'    => 'required|integer',
            'needs_value'   => 'required',
        ];
        $messages = [
            'id.required'           => 'id不能为空',
            'id.integer'            => 'id不是整数',
            'contact_id.required'   => '需求联系人id不能为空',
            'contact_id.integer'    => '需求联系人id不是整数',
            'needs_value.required'  => '需求内容不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberContactServices->editMemberContact($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->memberContactServices->error];
        }
        return ['code' => 200, 'message' => $this->memberContactServices->message];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/member/del_member_contact",
     *     tags={"成员联系申请"},
     *     summary="删除成员查看成员的联系请求",
     *     operationId="del_member_contact",
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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="id",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function delMemberContact(){
        $rules = [
            'id'  => 'required|integer',
        ];
        $messages = [
            'id.required' => 'id不能为空',
            'id.integer'  => 'id不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberContactServices->delMemberContact($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->memberContactServices->error];
        }
        return ['code' => 200, 'message' => $this->memberContactServices->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/member/get_member_contact",
     *     tags={"成员联系申请"},
     *     summary="获取成员查看成员的联系列表",
     *     operationId="get_member_contact",
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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getMemberContact(){
        $res = $this->memberContactServices->getMemberContact();
        if ($res == false){
            return ['code' => 100, 'message' => $this->memberContactServices->error];
        }
        return ['code' => 200, 'message' => $this->memberContactServices->message,'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/member/get_member_contact_info",
     *     tags={"成员联系申请"},
     *     summary="获取成员查看成员的联系详情",
     *     operationId="get_member_contact_info",
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
     *         description="用户 token",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="id",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getMemberContactInfo(){
        $rules = [
            'id'  => 'required|integer',
        ];
        $messages = [
            'id.required' => 'id不能为空',
            'id.integer'  => 'id不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberContactServices->getMemberContactInfo($this->request['id']);
        if ($res == false){
            return ['code' => 100, 'message' => $this->memberContactServices->error];
        }
        return ['code' => 200, 'message' => $this->memberContactServices->message,'data' => $res];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/member/get_member_contact_list",
     *     tags={"成员联系申请"},
     *     summary="OA  成员联系申请列表",
     *     operationId="get_member_contact_list",
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
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="审核类型【0 已提交 1已审核 2审核驳回 3取消预约】",
     *         required=false,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
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
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getMemberContactList(){
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
            'status'        => 'in:0,1,2,3',
        ];
        $messages = [
            'page.integer'      => '页码不是整数',
            'page_num.integer'  => '每页显示条数不是整数',
            'status.in'         => '审核类型不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberContactServices->getMemberContactList($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->memberContactServices->error];
        }
        return ['code' => 200, 'message' => $this->memberContactServices->message,'data' => $res];
    }


    /**
     * @OA\Get(
     *     path="/api/v1/member/get_member_contact_detail",
     *     tags={"成员联系申请"},
     *     summary="OA  成员联系申请详情",
     *     operationId="get_member_contact_detail",
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
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="申请ID",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getMemberContactDetail(){
        $rules = [
            'id'            => 'required|integer',
        ];
        $messages = [
            'id.required'   => '申请ID不能为空',
            'id.integer'    => '申请ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberContactServices->getMemberContactDetail($this->request['id']);
        if ($res == false){
            return ['code' => 100, 'message' => $this->memberContactServices->error];
        }
        return ['code' => 200, 'message' => $this->memberContactServices->message,'data' => $res];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/member/set_member_contact",
     *     tags={"成员联系申请"},
     *     summary="OA  审核成员查看成员的联系",
     *     operationId="set_member_contact",
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
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="id",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="审核结果【 1通过 2驳回】",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *         )
     *    ),
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function setMemberContact(){
        $rules = [
            'id'            => 'required|integer',
            'status'        => 'required|in:1,2',
        ];
        $messages = [
            'id.required'       => 'ID不能为空',
            'id.integer'        => 'ID必须为整数',
            'status.required'   => '审核结果不能为空',
            'status.in'         => '审核结果不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberContactServices->setMemberContact($this->request['id'],$this->request['status']);
        if ($res == false){
            return ['code' => 100, 'message' => $this->memberContactServices->error];
        }
        return ['code' => 200, 'message' => $this->memberContactServices->message];
    }
}