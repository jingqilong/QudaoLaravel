<?php


namespace App\Api\Controllers\V1\Common;


use App\Api\Controllers\ApiController;
use App\Services\Common\ImagesService;

class ImagesController extends ApiController
{
    public $imagesService;

    /**
     * ImagesController constructor.
     * @param $imagesService
     */
    public function __construct(ImagesService $imagesService)
    {
        parent::__construct();
        $this->imagesService = $imagesService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/common/get_image_repository",
     *     tags={"公共"},
     *     summary="获取图片仓库",
     *     description="sang" ,
     *     operationId="get_image_repository",
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
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="图片类型，0、公共，1、精彩活动，2、医疗特约，3、企业咨询，4、房产-租赁，
     *                      5、会员头像，6、项目对接，7、成员风采，8、精选生活，9、商城模块，10、私享空间",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="排序,desc倒叙，asc顺序",
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
     *     @OA\Response(
     *         response=200,
     *         description="获取成功",
     *         @OA\JsonContent(ref=""),
     *         @OA\MediaType(
     *             mediaType="application/xml",
     *             @OA\Schema(required={"code", "message"})
     *         ),
     *     ),
     *     @OA\Response(
     *         response="100",
     *         description="获取失败",
     *         @OA\JsonContent(ref=""),
     *     )
     * )
     *
     */
    public function getImageRepository(){
        $rules = [
            'order'         => 'in:desc,asc',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'order.integer'         => '排序取值有误',
            'page.integer'          => '页码必须为整数',
            'page_num.integer'      => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->imagesService->getImageRepository($this->request);
        if ($res === false){
            return ['code' => 100, 'message' => $this->imagesService->error];
        }
        return ['code' => 200, 'message' => $this->imagesService->message, 'data' => $res];
    }
}