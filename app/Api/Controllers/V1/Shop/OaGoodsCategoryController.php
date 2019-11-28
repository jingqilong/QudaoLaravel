<?php


namespace App\Api\Controllers\V1\Shop;


use App\Api\Controllers\ApiController;
use App\Services\Shop\GoodsCategoryService;

class OaGoodsCategoryController extends ApiController
{

    public $goodsCategoryService;

    /**
     * OaGoodsCategoryController constructor.
     * @param $goodsCategoryService
     */
    public function __construct(GoodsCategoryService $goodsCategoryService)
    {
        parent::__construct();
        $this->goodsCategoryService = $goodsCategoryService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/shop/add_category",
     *     tags={"商城后台"},
     *     summary="添加商品类别",
     *     description="sang" ,
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
     *         name="name",
     *         in="query",
     *         description="类别名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="icon_id",
     *         in="query",
     *         description="类别图标ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function addCategory(){
        $rules = [
            'name'              => 'required',
            'icon_id'           => 'required|integer'
        ];
        $messages = [
            'name.required'         => '类别名称不能为空',
            'icon_id.required'      => '类别图标不能为空',
            'icon_id.integer'       => '类别图标必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->goodsCategoryService->addCategory($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->goodsCategoryService->error];
        }
        return ['code' => 200, 'message' => $this->goodsCategoryService->message];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/shop/delete_category",
     *     tags={"商城后台"},
     *     summary="删除商品类别",
     *     description="sang" ,
     *     operationId="delete_category",
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
     *         description="类别ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function deleteCategory(){
        $rules = [
            'id'           => 'required|integer'
        ];
        $messages = [
            'id.required'      => '类别ID不能为空',
            'id.integer'       => '类别ID必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->goodsCategoryService->deleteCategory($this->request['id']);
        if ($res === false){
            return ['code' => 100,'message' => $this->goodsCategoryService->error];
        }
        return ['code' => 200, 'message' => $this->goodsCategoryService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/shop/edit_category",
     *     tags={"商城后台"},
     *     summary="修改商品类别",
     *     description="sang" ,
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
     *         description="类别ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="类别名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="icon_id",
     *         in="query",
     *         description="类别图标ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="修改失败",),
     * )
     *
     */
    public function editCategory(){
        $rules = [
            'id'                => 'required|integer',
            'name'              => 'required',
            'icon_id'           => 'required|integer'
        ];
        $messages = [
            'id.required'           => '类别ID不能为空',
            'id.integer'            => '类别ID必须为整数',
            'name.required'         => '类别名称不能为空',
            'icon_id.required'      => '类别图标不能为空',
            'icon_id.integer'       => '类别图标必须为整数'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->goodsCategoryService->editCategory($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->goodsCategoryService->error];
        }
        return ['code' => 200, 'message' => $this->goodsCategoryService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_category_list",
     *     tags={"商城后台"},
     *     summary="获取商品类别列表",
     *     description="sang" ,
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
     *         name="keywords",
     *         in="query",
     *         description="搜索，【类别名】",
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
     *     @OA\Response(response=100,description="获取失败",),
     * )
     *
     */
    public function getCategoryList(){
        $rules = [
            'page'              => 'integer',
            'page_num'          => 'integer',
        ];
        $messages = [
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->goodsCategoryService->getCategoryList($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->goodsCategoryService->error];
        }
        return ['code' => 200, 'message' => $this->goodsCategoryService->message,'data' => $res];
    }
}