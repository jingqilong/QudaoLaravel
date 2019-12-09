<?php
namespace App\Services\Shop;


use App\Repositories\ShopGoodsRepository;
use App\Repositories\ShopGoodsSpecRelateRepository;
use App\Repositories\ShopGoodsSpecRepository;
use App\Repositories\ShopOrderGoodsRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class GoodsSpecService extends BaseService
{

    /**
     * 使用json添加规格
     * @param $goods_id
     * @param $json_spec
     * @param string $goods_keywords    商品的搜索关键字
     * @return bool
     */
    public function addJsonSpec($goods_id, $json_spec, $goods_keywords = '')
    {
        $time = time();
        #检查商品是否已被购买，未购买则直接删除旧的规格
        if (!ShopOrderGoodsRepository::exists(['goods_id' => $goods_id])){
            ShopGoodsSpecRepository::delete(['goods_id' => $goods_id]);
            ShopGoodsSpecRelateRepository::delete(['goods_id' => $goods_id]);
        }else{
            ShopGoodsSpecRepository::getUpdId(['goods_id' => $goods_id],['deleted_at' => $time]);
            ShopGoodsSpecRelateRepository::getUpdId(['goods_id' => $goods_id],['deleted_at' => $time]);
        }
        $decode_spec = json_decode($json_spec,true);
        DB::beginTransaction();
        foreach ($decode_spec as $value){
            if (!isset($value['stock']) || !isset($value['price']) || !isset($value['spec'])){
                DB::rollBack();
                $this->setError('商品规格库存、价格、规格不能为空！');
                return false;
            }
            if (empty($value['spec'])){
                DB::rollBack();
                $this->setError('必须添加规格属性！');
                return false;
            }
            $spec_ids = ',';
            #添加规格
            foreach ($value['spec'] as $item){
                if (!isset($item['spec_name']) || !isset($item['spec_value'])){
                    DB::rollBack();
                    $this->setError('商品规格名称和值不能为空！');
                    return false;
                }
                $spec_where = ['goods_id'  => $goods_id, 'spec_name' => $item['spec_name'], 'spec_value'=> $item['spec_value'],];
                $add_arr    = ['created_at'=> $time, 'updated_at'=> $time];
                if (isset($item['image_id']) && !empty($item['image_id'])){
                    $add_arr['image_id'] = $item['image_id'];
                }
                $add_arr = array_merge($add_arr,$spec_where);
                if (!$spec = ShopGoodsSpecRepository::firstOrCreate($spec_where,$add_arr)){
                    DB::rollBack();
                    $this->setError('规格添加失败！');
                    return false;
                }
                if (!ShopGoodsSpecRepository::getUpdId(['id' => $spec['id']],['image_id'  => $item['image_id'] ?? 0,'deleted_at' => 0])){
                    DB::rollBack();
                    $this->setError('规格添加失败！');
                    return false;
                }
                $goods_keywords = !strpos($goods_keywords,$item['spec_name']) ? ($goods_keywords.$item['spec_name']) : $goods_keywords;
                $goods_keywords = !strpos($goods_keywords,$item['spec_value']) ? ($goods_keywords.$item['spec_value']) : $goods_keywords;
                $spec_ids = $spec_ids . $spec['id'] . ',';
            }
            #添加规格关联
            $spec_relate_where = ['goods_id'=>$goods_id,'spec_ids'=> $spec_ids,'stock'=> $value['stock'],'price'=> $value['price'] * 100,];
            $add_spec_relate = ['created_at'=> $time, 'updated_at'=> $time,];
            $add_spec_relate = array_merge($add_spec_relate,$spec_relate_where);
            if (!$spec_relate = ShopGoodsSpecRelateRepository::firstOrCreate($spec_relate_where,$add_spec_relate)){
                DB::rollBack();
                $this->setError('规格添加失败！');
                return false;
            }
            if (!ShopGoodsSpecRelateRepository::getUpdId(['id' => $spec_relate['id']],['deleted_at' => 0])){
                DB::rollBack();
                $this->setError('规格添加失败！');
                return false;
            }
        }
        ShopGoodsRepository::getUpdId(['id' => $goods_id],['keywords' => $goods_keywords]);
        DB::commit();
        $this->setMessage('添加成功！');
        return true;
    }
}
            