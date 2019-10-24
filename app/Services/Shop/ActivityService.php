<?php
namespace App\Services\Shop;


use App\Repositories\CommonImagesRepository;
use App\Repositories\ShopActivityRepository;
use App\Repositories\ShopGoodsCommonRepository;
use App\Repositories\ShopGoodsRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class ActivityService extends BaseService
{
    use HelpTrait;
    /**
     * 获取首页积分展示商品
     * @return array
     */
    public static function getHomeShow(){
        if (!$activity_goods = ShopActivityRepository::getOrderOne(['type' => 1,'status' => 2,'stop_time' => 0],'id')){
            return [];
        }
        $res = [
            'goods_id' => $activity_goods['goods_id'],
            'type' => $activity_goods['type'],
            'show_image' => CommonImagesRepository::getField(['id' => $activity_goods['show_image']],'img_url')
        ];
        return $res;
    }

    /**
     * 获取首页推荐商品信息
     * @return array|mixed
     */
    public static function getHomeRecommendGoods(){
        if (!$activity_goods = ShopActivityRepository::getList(['type' => 2,'status' => 2,])){
            return [];
        }
        $goods_ids = array_column($activity_goods,'goods_id');
        if (!$goods_list = ShopGoodsRepository::getList(['goods_id' => ['in',$goods_ids]],['goods_common_id','goods_price'])){
            return [];
        }
        $goods_common_ids = array_column($goods_list,'goods_common_id');
        $goods_common_column = ['goods_common_id','goods_name','main_img_id'];
        if (!$goods_common_list = ShopGoodsRepository::getList(['goods_common_id' => ['in',$goods_common_ids]],$goods_common_column)){
            return [];
        }
        $img_ids = array_column($goods_common_list,'main_img_id');
        $image_list = CommonImagesRepository::getList(['id' => ['in',$img_ids]]);
        foreach ($goods_common_list as &$value){
            $value['image'] = '';
            if ($image = self::searchArray($image_list,'id',$value['main_img_id'])){
                $value['image'] = reset($image)['img_url'];
            }
        }
        foreach ($goods_list as &$value){
            $value['goods_name'] = '';
            $value['goods_image'] = '';
            if ($image = self::searchArray($goods_common_list,'goods_common_id',$value['goods_common_id'])){
                $value['goods_name'] = reset($image)['goods_name'];
                $value['goods_image'] = reset($image)['image'];
            }
        }
        return $goods_list;
    }
}
            