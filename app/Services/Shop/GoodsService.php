<?php
namespace App\Services\Shop;


use App\Repositories\ShopGoodsCategoryRepository;
use App\Repositories\ShopGoodsRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class GoodsService extends BaseService
{

    /**
     * 添加商品
     * @param $request
     * @return bool
     */
    public function addGoods($request)
    {
        if (!ShopGoodsCategoryRepository::exists(['id' => $request['category']])){
            $this->setError('商品分类不存在！');
            return false;
        }
        if (isset($request['score_deduction']) && !isset($request['score_categories'])){
            $this->setError('可抵扣积分类型不能为空！');
            return false;
        }
        $add_arr = [
            'name'              => $request['name'],
            'category'          => $request['category'],
            'price'             => $request['price'] * 100,
            'banner_ids'        => $request['banner_ids'],
            'image_ids'         => $request['image_ids'],
            'stock'             => $request['stock'],
            'express_price'     => isset($request['stock']) ? $request['stock'] * 100 : 0,
            'score_deduction'   => $request['score_deduction'] ?? 0,
            'score_categories'  => $request['score_categories'] ?? '',
            'is_recommend'      => isset($request['is_recommend']) ? ($request['is_recommend'] == 1 ? time() : 0) : 0,
            'status'            => $request['status'],
        ];
        if (isset($request['gift_score'])){
            $add_arr['gift_score'] = $request['gift_score'];
        }
        if (ShopGoodsRepository::exists($add_arr)){
            $this->setError('该商品已添加！');
            return false;
        }
        $add_arr['created_at'] = $add_arr['updated_at'] = time();
        DB::beginTransaction();
        if (!$goods_id = ShopGoodsRepository::getAddId($add_arr)){
            DB::rollBack();
            $this->setError('该商品已添加！');
            return false;
        }
        #处理规格
        //TODO
        if (isset($request['spec_json'])){
            $goodsSpecService = new GoodsSpecService();
            if (!$goodsSpecService->addJsonSpec($goods_id,$request['spec_json'])){
                DB::rollBack();
                $this->setError($goodsSpecService->error);
                return false;
            }
        }
        //        $spec = [
//            ['stock' => 20,'price' => 500,'spec' => ['颜色' => '红色','容量' => '500','key' => 'value']],
//            ['stock' => 30,'price' => 900,'spec' => ['颜色' => '红色','容量' => '1000','key' => 'value2']],
//            ['stock' => 35,'price' => 500,'spec' => ['颜色' => '绿色','容量' => '500','key' => 'value']],
//            ['stock' => 20,'price' => 900,'spec' => ['颜色' => '绿色','容量' => '1000','key' => 'value2']],
//        ];
        DB::commit();
        $this->setMessage('添加成功！');
        return true;
    }
}
            