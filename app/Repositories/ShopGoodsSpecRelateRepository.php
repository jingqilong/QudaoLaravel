<?php


namespace App\Repositories;


use App\Models\ShopGoodsSpecRelateModel;
use App\Repositories\Traits\RepositoryTrait;
use App\Services\Common\ImagesService;
use Illuminate\Support\Arr;

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
     * 获取规格的库存数量 没有规格返回传进来的参数
     * @param $goods_id
     * @param $stock
     * @return float|int
     */
    protected function getStockCount($goods_id, $stock)
    {
        if ($spec_stock_arr = $this->getList(['goods_id' => $goods_id,'deleted_at' => 0],['stock'])){
            return array_sum(Arr::flatten($spec_stock_arr));
        }
        return $stock;
    }
}
            