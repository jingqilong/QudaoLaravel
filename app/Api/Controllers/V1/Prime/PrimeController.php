<?php


namespace App\Api\Controllers\V1\Prime;


use App\Api\Controllers\ApiController;
use App\Services\Prime\MerchantInfoService;

class PrimeController extends ApiController
{
    protected $merchantInfoService;

    /**
     * TestApiController constructor.
     * @param MerchantInfoService $merchantInfoService
     */
    public function __construct(MerchantInfoService $merchantInfoService)
    {
        parent::__construct();
        $this->merchantInfoService = $merchantInfoService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/prime/get_home_list",
     *     tags={"精选生活"},
     *     summary="获取首页列表",
     *     description="sang" ,
     *     operationId="get_home_list",
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
     *         description="会员token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="keywords",
     *         in="query",
     *         description="搜索关键字，【名称、短标题】",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="类别，【1健身，2餐饮，3宾馆】",
     *         required=false,
     *         @OA\Schema(
     *             type="integer"
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
     *     @OA\Response(
     *         response=100,
     *         description="预约失败",
     *     ),
     *     ),
     * )
     *
     */
    public function getHomeList(){
        $rules = [
            'keywords'      => 'string',
            'type'          => 'in:1,2,3',
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'keywords.string'           => '关键字类型不正确',
            'type.in'                   => '商户类别不存在',
            'page.integer'              => '页码不是整数',
            'page_num.integer'          => '每页显示条数不是整数',
        ];

        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->merchantInfoService->getHomeList($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->merchantInfoService->message, 'data' => $res];
        }
        return ['code' => 100, 'message' => $this->merchantInfoService->error];
    }
}