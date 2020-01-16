<?php


namespace App\Repositories;


use App\Models\ShopOrderGoodsModel;
use App\Repositories\Traits\RepositoryTrait;
use Illuminate\Support\Arr;

class ShopOrderGoodsRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ShopOrderGoodsModel $model)
    {
        $this->model = $model;
    }

    /**
     * 添加订单商品
     * @param array $goods_info     要添加的商品信息，必须包含字段，$goods_info = ['goods_id','number','spec_relate_id']
     * @param int $order_relate_id  订单ID
     * @return bool
     */
    protected function addOrderGoods($goods_info, $order_relate_id){
        $order_relate_add_arr = ShopGoodsSpecRepository::bulkHasOneWalk(
            $goods_info,
            ['from' => 'spec_relate_id','to'=>'id'],
            ['id','goods_id','spec_name','spec_value'],
            [],
            function($src_item) use($order_relate_id) {
                $src_item['order_relate_id']    = $order_relate_id;
                $src_item['spec_relate_value']  = $src_item['spec'] ?? '';
                $spec_arr = Arr::only($src_item,['goods_id','order_relate_id','spec_relate_id','spec_relate_value','number']);
                $spec_arr['created_at']         = time();
                return $spec_arr;
            }
        );
        if (!$this->create($order_relate_add_arr)){
            return false;
        }
        return true;
    }
}
            