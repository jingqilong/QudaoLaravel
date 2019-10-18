<?php


namespace App\Api\Controllers\V1;


use App\Api\Controllers\ApiController;
use App\Services\Common\QiNiuService;

class QiNiuController extends ApiController
{
    public $qiNiuService;

    /**
     * QiNiuController constructor.
     * @param $qiNiuService
     */
    public function __construct(QiNiuService $qiNiuService)
    {
        parent::__construct();
        $this->qiNiuService = $qiNiuService;
    }

    /**
     * 本地图片迁移至七牛云
     */
    public function imagesMigration(){
        set_time_limit(300);
        $res = $this->qiNiuService->migrationFile();
        return ['code' => 200, 'message' => '迁移成功', 'data' => $res];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/qiniu/upload_images",
     *     tags={"七牛云"},
     *     summary="上传至七牛云",
     *     description="sang" ,
     *     operationId="upload_images",
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
     *         name="storage_space",
     *         in="query",
     *         description="存储空间类别【1、精彩活动，2、医疗特约，3、企业咨询，4、房产-租赁，
     *                      5、会员头像，6、项目对接，7、成员风采，8、精选生活，9、商城模块，10、私享空间】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="images",
     *         in="query",
     *         description="文件【可最多一次性上传20张图片】",
     *         required=true,
     *         @OA\Schema(
     *             type="file"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="上传失败",
     *     ),
     * )
     *
     */
    /**
     * Get a JWT via given credentials.
     *
     * @return array
     */
    public function uploadImages(){
        $rules = [
            'storage_space' => 'required|integer',
        ];
        $messages = [
            'storage_space.required' => '请输入存储空间类别',
            'storage_space.integer'  => '存储空间类别必须为整数',
        ];
        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->qiNiuService->upload($this->request['storage_space']);
        if ($res['code'] == 0){
            return ['code' => 100, 'message' => $res['message']];
        }
        return ['code' => 200, 'message' => $res['message'], 'data' => $res['data']];
    }
}