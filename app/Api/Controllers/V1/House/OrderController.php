<?php


namespace App\Api\Controllers\V1\House;


use App\Api\Controllers\ApiController;
use App\Services\House\OrderService;

class OrderController extends ApiController
{
    protected $orderService;

    /**
     * OrderController constructor.
     * @param OrderService $orderService
     */
    public function __construct(OrderService $orderService)
    {
        parent::__construct();
        $this->orderService = $orderService;
    }

    public function addHouseOrder()
    {
        $rules = [
            'or_user'       => 'required',
            'or_uphone'     => 'required|mobile',
            'or_eid'        => 'required|unique:qd_house_estate',
            'or_openid'     => 'required',
            'or_utime'      => 'required|datetime',
            'or_content'    => 'required',
        ];
        $messages = [
            'name.required' => '未找到审核类型',
            'name.unique'   => '重复的审核类型，请重新输入',
            'url.active_url'     => '不是有效的网址',
        ];

        // 验证参数，如果验证失败，则会抛出 ValidationException 的异常
        $Validate = $this->ApiValidate($rules, $messages);
        if ($Validate->fails()){
            return ['code' => 100, 'message' => $this->error];
        }
    }
}