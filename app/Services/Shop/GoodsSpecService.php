<?php
namespace App\Services\Shop;


use App\Services\BaseService;

class GoodsSpecService extends BaseService
{

    /**
     * 使用json添加规格
     * @param $goods_id
     * @param $json_spec
     * @return bool
     */
    public function addJsonSpec($goods_id, $json_spec)
    {
        $decode_spec = json_decode($json_spec,true);
        foreach ($decode_spec as $value){
            if (!isset($value['stock']) || !isset($value['price']) || !isset($value['spec'])){
                $this->setError('商品规格json格式有误！');
                return false;
            }
            $stock = $value['stock'];
            $price = $value['price'];
            $spec  = $value['spec'];
            if (empty($spec)){
                $this->setError('必须添加规格属性！');
                return false;
            }
            foreach ($spec as $value){
                $add_arr = [
                    'goods_id'  => $goods_id,
                    'image_id'  => $value['image_id'],
                    'spec_name' => $value['spec_name'],
                    'spec_value'=> $value['spec_value']
                ];
            }
        }
        $this->setMessage('添加成功！');
        return true;
    }
}
            