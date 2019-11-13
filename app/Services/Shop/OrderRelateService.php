<?php
namespace App\Services\Shop;


use App\Repositories\ShopGoodsRepository;
use App\Services\BaseService;

class OrderRelateService extends BaseService
{

    public function getPlaceOrderDetail($request)
    {
        $goods_info = json_decode($request['goods_json'],true);
        foreach ($goods_info as $item){
            if (!isset($item['goods_id']) || !isset($item['number'])){
                $this->setError('商品ID和数量不能为空！');
                return false;
            }
        }
        dd($goods_info);
        if (!$goods = ShopGoodsRepository::getOne(['id' => $request['goods_id']])){
            $this->setError('商品不存在！');
            return false;
        }
    }
}
            