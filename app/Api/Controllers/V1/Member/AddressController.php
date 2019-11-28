<?php
/**
 * Created By PhpStorm
 * User: jql
 * Date: 2019/11/11
 * Time: 15:55
 */

namespace App\Api\Controllers\V1\Member;


use App\Api\Controllers\ApiController;
use App\Services\Member\AddressService;

class AddressController extends ApiController
{
    public $memberAddressService;

    /**
     * AddressController constructor.
     * @param $memberAddressService
     */
    public function __construct(AddressService $memberAddressService)
    {
        parent::__construct();
        $this->memberAddressService = $memberAddressService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/member/add_address",
     *     tags={"用户地址管理"},
     *     summary="用户添加地址",
     *     description="jing" ,
     *     operationId="add_address",
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
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="收件人姓名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="收件人手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="area_code",
     *         in="query",
     *         description="收件人 地址地区代码，例如：【310000,310100,310106,310106013】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="详细地址，例如：延安西路300号",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="default",
     *         in="query",
     *         description="默认地址，1 默认地址  默认0（非默认地址）",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    public function addAddress()
    {
        $rules = [
            'name'      => 'required',
            'mobile'    => 'required|regex:/^1[345678][0-9]{9}$/',
            'area_code' => 'required|regex:/^(\d+[,])*\d+$/',
            'address'   => 'required',
            'default'   => 'in:0,1',
        ];
        $messages = [
            'name.required'         => '收件人姓名不能为空',
            'mobile.required'       => '收件人手机号码不能为空',
            'mobile.regex'          => '手机号码格式不正确',
            'area_code.required'    => '地区编码不能为空',
            'area_code.regex'       => '地区编码格式有误',
            'address.required'      => '详细地址不能为空',
            'default.in'            => '设置默认类性值不正确',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()) {
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberAddressService->addAddress($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->memberAddressService->message];
        }
        return ['code' => 100, 'message' => $this->memberAddressService->error];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/member/del_address",
     *     tags={"用户地址管理"},
     *     summary="用户删除地址",
     *     description="jing" ,
     *     operationId="del_address",
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
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="地址id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="删除失败",
     *     ),
     * )
     *
     */
    public function delAddress()
    {
        $rules = [
            'id'            => 'required|integer',
        ];
        $messages = [
            'id.required'   => '地址ID不能为空',
            'id.integer'    => '地址ID必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()) {
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberAddressService->delAddress($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->memberAddressService->message];
        }
        return ['code' => 100, 'message' => $this->memberAddressService->error];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/member/edit_address",
     *     tags={"用户地址管理"},
     *     summary="用户修改地址",
     *     description="jing" ,
     *     operationId="edit_address",
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
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="地址id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="收件人姓名",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="mobile",
     *         in="query",
     *         description="收件人手机号",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="area_code",
     *         in="query",
     *         description="收件人 地址地区代码，例如：【310000,310100,310106,310106013】",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="address",
     *         in="query",
     *         description="详细地址，例如：延安西路300号",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="default",
     *         in="query",
     *         description="默认地址，1 默认地址  默认0（非默认地址）",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    public function editAddress()
    {
        $rules = [
            'id'        => 'required|integer',
            'name'      => 'required',
            'mobile'    => 'required|regex:/^1[345678][0-9]{9}$/',
            'area_code' => 'required|regex:/^(\d+[,])*\d+$/',
            'address'   => 'required',
            'default'   => 'in:0,1',
        ];
        $messages = [
            'id.required'           => '地址id不能为空',
            'id.integer'            => '地址id必须为整数',
            'name.required'         => '收件人姓名不能为空',
            'mobile.required'       => '收件人手机号码不能为空',
            'mobile.regex'          => '手机号码格式不正确',
            'area_code.required'    => '地区编码不能为空',
            'area_code.regex'       => '地区编码格式有误',
            'address.required'      => '详细地址不能为空',
            'default.in'            => '设置默认类性值不正确',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()) {
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberAddressService->editAddress($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->memberAddressService->message];
        }
        return ['code' => 100, 'message' => $this->memberAddressService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/member/address_list",
     *     tags={"用户地址管理"},
     *     summary="获取用户地址",
     *     description="jing" ,
     *     operationId="address_list",
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
     *     @OA\Response(
     *         response=100,
     *         description="删除失败",
     *     ),
     * )
     *
     */
    public function addressList()
    {
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'page.integer'              => '页码不是整数',
            'page_num.integer'          => '每页显示条数不是整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()) {
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberAddressService->addressList($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->memberAddressService->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->memberAddressService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/member/list_address",
     *     tags={"用户地址管理(OA)"},
     *     summary="获取用户地址(OA)",
     *     description="jing" ,
     *     operationId="list_address",
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
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索关键字【1姓名  2手机号】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="asc",
     *         in="query",
     *         description="排序方式【1正序(默认) 2倒叙】",
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
     *     @OA\Response(
     *         response=100,
     *         description="删除失败",
     *     ),
     * )
     *
     */
    public function listAddress()
    {
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
            'asc'           => 'in:1,2',
        ];
        $messages = [
            'page.integer'              => '页码不是整数',
            'page_num.integer'          => '每页显示条数不是整数',
            'asc.in'                    => '排序类型不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()) {
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->memberAddressService->listAddress($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->memberAddressService->message,'data' => $res];
        }
        return ['code' => 100, 'message' => $this->memberAddressService->error];
    }
}