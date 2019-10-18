<?php


namespace App\Api\Controllers\V1\Activity;


use App\Api\Controllers\ApiController;
use App\Services\Activity\ThemeService;

class ThemeController extends ApiController
{
    public $themeService;

    /**
     * ThemeController constructor.
     * @param $themeService
     */
    public function __construct(ThemeService $themeService)
    {
        parent::__construct();
        $this->themeService = $themeService;
    }


    /**
     * @OA\Post(
     *     path="/api/v1/activity/add_activity_theme",
     *     tags={"精选活动后台"},
     *     summary="添加活动主题",
     *     description="sang" ,
     *     operationId="add_activity_theme",
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
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="主题名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="主题说明",
     *         required=false,
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
    public function addActivityTheme(){
        $rules = [
            'name'          => 'required',
        ];
        $messages = [
            'name.required'         => '主题名称不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->themeService->addTheme($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->themeService->message];
        }
        return ['code' => 100, 'message' => $this->themeService->error];
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/activity/delete_activity_theme",
     *     tags={"精选活动后台"},
     *     summary="删除活动主题",
     *     description="sang" ,
     *     operationId="delete_activity_theme",
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
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="主题id",
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
    public function deleteActivityTheme(){
        $rules = [
            'id'          => 'required',
        ];
        $messages = [
            'id.required'         => '主题ID不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->themeService->deleteTheme($this->request['id']);
        if ($res){
            return ['code' => 200, 'message' => $this->themeService->message];
        }
        return ['code' => 100, 'message' => $this->themeService->error];
    }


    /**
     * @OA\Post(
     *     path="/api/v1/activity/edit_activity_theme",
     *     tags={"精选活动后台"},
     *     summary="修改活动主题",
     *     description="sang" ,
     *     operationId="edit_activity_theme",
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
     *         description="token",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="主题ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="主题名称",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="主题说明",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=100,
     *         description="修改失败",
     *     ),
     * )
     *
     */
    public function editActivityTheme(){
        $rules = [
            'id'            => 'required|integer',
            'name'          => 'required',
        ];
        $messages = [
            'id.required'           => '主题ID不能为空',
            'id.integer'            => '主题ID必须为整数',
            'name.required'         => '主题名称不能为空',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->themeService->editTheme($this->request);
        if ($res){
            return ['code' => 200, 'message' => $this->themeService->message];
        }
        return ['code' => 100, 'message' => $this->themeService->error];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/activity/activity_theme_list",
     *     tags={"精选活动后台"},
     *     summary="获取活动主题列表",
     *     description="sang" ,
     *     operationId="activity_theme_list",
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
     *         description="token",
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
     *         description="修改失败",
     *     ),
     * )
     *
     */
    public function activityThemeList(){
        $rules = [
            'page'          => 'integer',
            'page_num'      => 'integer',
        ];
        $messages = [
            'page.integer'              => '页码必须为整数',
            'page_num.integer'          => '每页显示条数必须为整数',
        ];
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
        $res = $this->themeService->getThemeList(($this->request['page'] ?? 1),($this->request['page_num'] ?? 20));
        if ($res === false){
            return ['code' => 100, 'message' => $this->themeService->error];
        }
        return ['code' => 200, 'message' => $this->themeService->message,'data' => $res];
    }

}