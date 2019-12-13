<?php
namespace App\Api\Controllers\V1\Message;

use App\Api\Controllers\ApiController;
use App\Services\Message\CategoryService;

class MessageCategoryController extends ApiController
{
    public $categoryService;

    /**
     * MessageCategoryController constructor.
     * @param $categoryService
     */
    public function __construct(CategoryService $categoryService)
    {
        parent::__construct();
        $this->categoryService = $categoryService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/message/add_category",
     *     tags={"消息后台"},
     *     summary="添加消息分类",
     *     operationId="add_category",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="类型标签",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="类型状态，默认0正常，1禁用",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="view",
     *         in="query",
     *         description="跳转页面",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="explain",
     *         in="query",
     *         description="类型说明",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function addCategory(){
        $rules = [
            'title'             => 'required',
            'status'            => 'required|in:0,1',
            'explain'           => 'required|max:500'
        ];
        $messages = [
            'title.required'        => '类型标签不能为空',
            'status.required'       => '类型状态不能为空',
            'status.in'             => '类型状态取值有误',
            'explain.required'      => '类型说明不能为空',
            'explain.max'           => '类型说明不能超过500字'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->categoryService->addCategory($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->categoryService->error];
        }
        return ['code' => 200, 'message' => $this->categoryService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/message/disable_category",
     *     tags={"消息后台"},
     *     summary="禁用或开启消息分类",
     *     operationId="disable_category",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="类型ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="操作失败",),
     * )
     *
     */
    public function disableCategory(){
        $rules = [
            'id'        => 'required|integer'
        ];
        $messages = [
            'id.required'       => '类型ID不能为空',
            'id.integer'        => '类型ID必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->categoryService->disableCategory($this->request['id']);
        if ($res === false){
            return ['code' => 100,'message' => $this->categoryService->error];
        }
        return ['code' => 200, 'message' => $this->categoryService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/message/edit_category",
     *     tags={"消息后台"},
     *     summary="编辑消息分类",
     *     operationId="edit_category",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="类型ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="类型标签",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="类型状态，默认0正常，1禁用",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="view",
     *         in="query",
     *         description="跳转页面",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="explain",
     *         in="query",
     *         description="类型说明",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="修改失败",),
     * )
     *
     */
    public function editCategory(){
        $rules = [
            'id'                => 'required|integer',
            'title'             => 'required',
            'status'            => 'required|in:0,1',
            'explain'           => 'required|max:500'
        ];
        $messages = [
            'id.required'           => '类型ID不能为空',
            'id.integer'            => '类型ID必须为整数',
            'title.required'        => '类型标签不能为空',
            'status.required'       => '类型状态不能为空',
            'status.in'             => '类型状态取值有误',
            'explain.required'      => '类型说明不能为空',
            'explain.max'           => '类型说明不能超过500字'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->categoryService->editCategory($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->categoryService->error];
        }
        return ['code' => 200, 'message' => $this->categoryService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/message/get_category_list",
     *     tags={"消息后台"},
     *     summary="获取消息分类列表",
     *     operationId="get_category_list",
     *     @OA\Parameter(
     *          name="sign",
     *          in="query",
     *          description="签名",
     *          required=true,
     *          @OA\Schema(
     *          type="string",
     *          )
     *      ),
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="OA token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="类型状态，默认0正常，1禁用",
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
    public function getCategoryList(){
        $rules = [
            'status'            => 'in:0,1',
            'page'              => 'integer',
            'page_num'          => 'integer'
        ];
        $messages = [
            'status.in'             => '消息状态取值有误',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->categoryService->getCategoryList($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->categoryService->error];
        }
        return ['code' => 200, 'message' => $this->categoryService->message,'data' => $res];
    }
}