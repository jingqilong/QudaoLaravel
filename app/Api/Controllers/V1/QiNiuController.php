<?php


namespace App\Api\Controllers\V1;


use App\Api\Controllers\ApiController;
use App\Services\Common\ImagesService;
use App\Services\Common\QiNiuService;

class QiNiuController extends ApiController
{
    public $qiNiuService;
    public $imagesService;

    /**
     * QiNiuController constructor.
     * @param QiNiuService $qiNiuService
     * @param ImagesService $imagesService
     */
    public function __construct(QiNiuService $qiNiuService,ImagesService $imagesService)
    {
        parent::__construct();
        $this->qiNiuService = $qiNiuService;
        $this->imagesService = $imagesService;
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
    /**
     * @OA\Post(
     *     path="/api/v1/qiniu/add_resource",
     *     tags={"七牛云"},
     *     summary="添加资源到资源库",
     *     description="sang" ,
     *     operationId="add_resource",
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
     *                      5、会员头像，6、项目对接，7、成员风采，8、精选生活，9、商城模块，10、私享空间，11，活动视频】",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="file_type",
     *         in="query",
     *         description="文件类别【1图片 2视频】",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="url",
     *         in="query",
     *         description="资源url",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="添加失败",
     *     ),
     * )
     *
     */
    /**
     * Get a JWT via given credentials.
     *
     * @return array
     */
    public function addResource(){
        $rules = [
            'storage_space' => 'required|integer',
            'url'           => 'required|url',
            'file_type'     => 'required|in:1,2',
        ];
        $messages = [
            'storage_space.required' => '请输入存储空间类别',
            'storage_space.integer'  => '存储空间类别必须为整数',
            'url.required'           => '请输入资源url',
            'url.url'                => '资源url不是一个合法的链接',
            'file_type.required'     => '文件类别不能为空',
            'file_type.in'           => '文件类别不存在',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->imagesService->addResource($this->request);
        if ($res == false){
            return ['code' => 100, 'message' => $this->imagesService->error];
        }
        return ['code' => 200, 'message' => $this->imagesService->message, 'data' => $res];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/qiniu/get_upload_token",
     *     tags={"七牛云"},
     *     summary="获取七牛云上传token",
     *     description="sang" ,
     *     operationId="get_upload_token",
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
     *     @OA\Response(
     *         response=100,
     *         description="获取失败",
     *     ),
     * )
     *
     */
    public function getUploadToken(){
        $rules = [
            'storage_space' => 'required|integer',
        ];
        $messages = [
            'storage_space.required' => '请输入存储空间类别',
            'storage_space.integer'  => '存储空间类别必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->qiNiuService->getUploadToken($this->request['storage_space']);
        if ($res == false){
            return ['code' => 100, 'message' => $this->qiNiuService->error];
        }
        return ['code' => 200, 'message' => $this->qiNiuService->message, 'data' => $res];
    }
}