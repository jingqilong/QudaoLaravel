<?php
namespace App\Services\Shop;


use App\Repositories\ShopGoodsRepository;
use App\Repositories\ShopGoodsSpecRelateRepository;
use App\Repositories\ShopGoodsSpecRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;

class GoodsSpecRelateService extends BaseService
{
    use HelpTrait;


    /**
     * 获取列表公共信息
     * @param array $spec_relate_ids
     * @param array $goods_column
     * @return array|bool
     */
    protected function getListCommonInfo(array $spec_relate_ids, $goods_column=['id','name','price','banner_ids']){
        if (!$spec_relate_list = ShopGoodsSpecRelateRepository::getList(['id' => ['in',$spec_relate_ids]])){
            $this->setError('数据异常，规格关联信息未找到!');
            return false;
        }
        $goods_ids  = array_column($spec_relate_list,'goods_id');
        $goods_list = ShopGoodsRepository::getAssignList($goods_ids,$goods_column);
        $goods_list = ImagesService::getListImages($goods_list,['banner_ids' => 'single']);
        $spec_ids   = implode(',',array_column($spec_relate_list,'spec_ids'));
        $spec_list  = ShopGoodsSpecRepository::getAssignList(explode(',',$spec_ids),['id','spec_value']);
        $result     = [];
        foreach ($spec_relate_list as $key => $value){
            $result[$key]['spec_relate_id'] = $value['id'];
            if ($goods  = $this->searchArray($goods_list,'id',$value['goods_id'])){
                $price  =  reset($goods)['price'];
                $result[$key] = [
                    'goods_name'  => reset($goods)['name'],
                    'goods_price' => round($price / 100,2),
                    'main_img_url' => reset($goods)['banner_url'],
                ];
            }
            $value_spec_ids = explode(',',trim($value['spec_ids'],','));
            $spec_str       = '';
            foreach ($value_spec_ids as $value_spec_id){
                if ($item_spec  = $this->searchArray($spec_list,'id',$value_spec_id)){
                    $spec_str   .= reset($item_spec)['spec_value'];
                }
            }
            $result[$key]['spec'] = $spec_str;
        }
        $this->setMessage('获取成功!');
        return $result;
    }
}
            