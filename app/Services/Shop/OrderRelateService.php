<?php
namespace App\Services\Shop;


use App\Repositories\ShopGoodsRepository;
use App\Repositories\ShopGoodsSpecRelateRepository;
use App\Repositories\ShopGoodsSpecRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;

class OrderRelateService extends BaseService
{
    use HelpTrait;

    /**
     * @param $request
     * @return mixed
     */
    public function getPlaceOrderDetail($request)
    {
        $goods_param        = json_decode($request['goods_json'],true);
        $res['address']     = [];
        $res['goods_info']  = GoodsSpecRelateService::getListCommonInfo($goods_param);
        $this->setMessage('获取成功！');
        return $res;
    }
}
            