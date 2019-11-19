<?php


namespace App\Repositories;


use App\Models\ShopGoodsSpecRelateModel;
use App\Repositories\Traits\RepositoryTrait;

class ShopGoodsSpecRelateRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopGoodsSpecRelateModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取商品 库存
     * @param $goods_id
     * @return int|null
     */
    protected function getStock($goods_id)
    {
        if ($this->exists(['goods_id' => $goods_id])){
            $stock = empty($this->getOne(['goods_id' => $goods_id],['stock'])) ? 0 : $this->getOne(['goods_id' => $goods_id],['stock']);
        }else{
            $stock = empty(ShopGoodsSpecRelateRepository::sum(['goods_id' => $goods_id], 'stock')) ? 0 : ShopGoodsSpecRelateRepository::sum(['goods_id' => $goods_id], 'stock');
        }
        return $stock;
    }
}
            