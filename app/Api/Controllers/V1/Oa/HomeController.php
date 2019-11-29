<?php


namespace App\Api\Controllers\V1\Oa;


use App\Api\Controllers\ApiController;
use App\Services\Common\PvService;

class HomeController extends ApiController
{
    public $pvService;

    /**
     * HomeController constructor.
     * @param $pvService
     */
    public function __construct(PvService $pvService)
    {
        parent::__construct();
        $this->pvService = $pvService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/oa/get_site_pv",
     *     tags={"OA"},
     *     summary="获取访问量",
     *     description="sang" ,
     *     operationId="get_site_pv",
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
     *         description="类型，1、天，2、周，3、月，4、年",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="100",
     *         description="获取失败",
     *         @OA\JsonContent(ref=""),
     *     )
     * )
     *
     */
    public function getSitePv(){
        $rules = [
            'type'          => 'required|in:1,2,3,4',
        ];
        $messages = [
            'type.required'     => '类型不能为空',
            'type.in'           => '类型不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->pvService->getSitePv($this->request['type']);
        if ($res === false){
            return ['code' => 100,'message' => $this->pvService->error];
        }
        return ['code' => 200,'message' => $this->pvService->message,'date' => $res];
    }
}