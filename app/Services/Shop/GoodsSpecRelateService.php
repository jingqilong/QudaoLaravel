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
     * 获取列表商品公共信息
     * @param $goods_spec_arr
     * @param array $goods_column
     * @return array|bool
     */
    protected function getListCommonInfo($goods_spec_arr, $goods_column=['id','name','price','banner_ids']){
        foreach ($goods_spec_arr as $value){
            if (!isset($value['goods_id']) || !isset($value['number'])){
                $this->setError('商品ID和数量不能为空！');
                return false;
            }
        }
        $spec_relate_ids    = array_column($goods_spec_arr,'spec_relate_id');
        $spec_relate_list   = empty($spec_relate_ids) ? [] : ShopGoodsSpecRelateRepository::getList(['id' => ['in',$spec_relate_ids],'deleted_at' => 0]);
        $goods_ids          = array_column($goods_spec_arr,'goods_id');
        $goods_list         = ShopGoodsRepository::getAssignList($goods_ids,$goods_column);
        $goods_list         = ImagesService::getListImages($goods_list,['banner_ids' => 'single']);
        $spec_ids           = implode(',',array_column($spec_relate_list,'spec_ids'));
        $spec_list          = ShopGoodsSpecRepository::getAssignList(explode(',',$spec_ids),['id','spec_value','spec_name']);
        $result             = [];
        foreach ($goods_spec_arr as $key => $value){
            if ($goods  = $this->searchArray($goods_list,'id',$value['goods_id'])){
                $price  =  reset($goods)['price'];
                $result[$key] = [
                    'goods_name'  => reset($goods)['name'],
                    'goods_price' => sprintf('%.2f',round($price / 100,2)),
                    'main_img_url' => reset($goods)['banner_url'],
                ];
            }
            $spec_str       = '';
            if ($spec_relate = $this->searchArray($spec_relate_list,'id',$value['spec_relate_id'])){
                $value_spec_ids = explode(',',trim(reset($spec_relate)['spec_ids'],','));
                foreach ($value_spec_ids as $value_spec_id){
                    if ($item_spec  = $this->searchArray($spec_list,'id',$value_spec_id)){
                        $spec_str  .= reset($item_spec)['spec_name'] .':'. reset($item_spec)['spec_value'] . ';';
                    }
                }
            }
            if (isset($value['order_relate_id'])){
                $result[$key]['order_relate_id']       = $value['order_relate_id'];
            }
            if (isset($value['cart_id'])){
                $result[$key]['cart_id']       = $value['cart_id'];
            }
            $result[$key]['goods_id']       = $value['goods_id'];
            $result[$key]['spec_relate_id'] = isset($value['spec_relate_id']) ? $value['spec_relate_id'] : 0;
            $result[$key]['spec']           = $spec_str;
            $result[$key]['number']         = $value['number'];
        }
        $this->setMessage('获取成功!');
        return $result;
    }
}
            