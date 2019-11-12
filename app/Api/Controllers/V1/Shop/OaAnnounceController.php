<?php


namespace App\Api\Controllers\V1\Shop;


use App\Api\Controllers\ApiController;
use App\Services\Shop\AnnounceService;
use App\Services\Shop\GoodsCategoryService;

class OaAnnounceController extends ApiController
{

    public $announceService;

    /**
     * OaAnnounceController constructor.
     * @param $announceService
     */
    public function __construct(AnnounceService $announceService)
    {
        parent::__construct();
        $this->announceService = $announceService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/shop/add_announce",
     *     tags={"商城后台"},
     *     summary="添加首页公告",
     *     operationId="add_announce",
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
     *         name="content",
     *         in="query",
     *         description="公告内容",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="添加失败",),
     * )
     *
     */
    public function addAnnounce(){
        $rules = [
            'content'       => 'required|max:500',
        ];
        $messages = [
            'content.required'      => '公告内容不能为空',
            'content.max'           => '公告内容不能超过500字'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->announceService->addAnnounce($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->announceService->error];
        }
        return ['code' => 200, 'message' => $this->announceService->message];
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/shop/delete_announce",
     *     tags={"商城后台"},
     *     summary="删除公告",
     *     operationId="delete_announce",
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
     *         description="公告ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(response=100,description="删除失败",),
     * )
     *
     */
    public function deleteAnnounce(){
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

        $res = $this->announceService->deleteAnnounce($this->request['id']);
        if ($res === false){
            return ['code' => 100,'message' => $this->announceService->error];
        }
        return ['code' => 200, 'message' => $this->announceService->message];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/shop/edit_announce",
     *     tags={"商城后台"},
     *     summary="修改公告",
     *     operationId="edit_announce",
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
     *         description="公告ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="content",
     *         in="query",
     *         description="公告内容",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(response=100,description="修改失败",),
     * )
     *
     */
    public function editAnnounce(){
        $rules = [
            'id'            => 'required|integer',
            'content'       => 'required|max:500',
        ];
        $messages = [
            'id.required'           => '公告ID不能为空',
            'id.integer'            => '公告ID必须为整数',
            'content.required'      => '公告内容不能为空',
            'content.max'           => '公告内容不能超过500字'
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }

        $res = $this->announceService->editAnnounce($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->announceService->error];
        }
        return ['code' => 200, 'message' => $this->announceService->message];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/shop/get_announce_list",
     *     tags={"商城后台"},
     *     summary="获取公告列表",
     *     operationId="get_announce_list",
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
     *         description="搜索，【公告内容】",
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
    public function getAnnounceList(){
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

        $res = $this->announceService->getAnnounceList($this->request);
        if ($res === false){
            return ['code' => 100,'message' => $this->announceService->error];
        }
        return ['code' => 200, 'message' => $this->announceService->message,'data' => $res];
    }
}