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
//            self::setMessage('没有活动商品！');
            return [];
        }
        $goods_ids = array_column($activity_goods,'goods_id');
        if (!$goods_list = ShopGoodsRepository::getList(['goods_id' => ['in',$goods_ids]],['goods_common_id','goods_price'])){
//            self::setMessage('活动商品不存在！');
            return [];
        }
        $goods_common_ids = array_column($goods_list,'goods_common_id');
        $goods_common_column = ['goods_common_id','goods_name','main_img_id'];
        if (!$goods_common_list = ShopGoodsCommonRepository::getList(['goods_common_id' => ['in',$goods_common_ids]],$goods_common_column)){
//            self::setMessage('活动商品信息不存在！');
            return [];
        }
        $img_ids = array_column($goods_common_list,'main_img_id');
        $image_list = CommonImagesRepository::getList(['id' => ['in',$img_ids]]);
        foreach ($goods_common_list as &$value){
            $value['image'] = '';
            if ($image = self::searchArrays($image_list,'id',$value['main_img_id'])){
                $value['image'] = reset($image)['img_url'];
            }
        }
        foreach ($goods_list as &$value){
            $value['goods_name'] = '';
            $value['goods_image'] = '';
            if ($image = self::searchArrays($goods_common_list,'goods_common_id',$value['goods_common_id'])){
                $value['goods_name'] = reset($image)['goods_name'];
                $value['goods_image'] = reset($image)['image'];
            }
        }
//        self::setMessage('获取成功！');
        return $goods_list;
    }

    /**
     * 添加活动商品
     * @param $request
     * @return bool
     */
    public function addActivityGoods($request)
    {
        if (!ShopGoodsRepository::exists(['goods_id' => $request['goods_id']])){
            $this->setError('该商品不存在！');
            return false;
        }
        $add_arr = [
            'goods_id'      => $request['goods_id'],
            'type'          => $request['type'],
            'status'        => $request['status'],
            'show_image'    => $request['show_image'],
        ];
        if (ShopActivityRepository::exists($add_arr)){
            $this->setError('该商品已添加！');
            return false;
        }
        $add_arr = array_merge($add_arr,['created_at'    => time(),'updated_at'    => time()]);
        if (!ShopActivityRepository::getAddId($add_arr)){
            $this->setError('添加失败！');
            return false;
        }
        $this->setMessage('添加成功！');
        return true;
    }

    /**
     * 修改商品活动记录
     * @param $request
     * @return bool
     */
    public function editActivityGoods($request)
    {
        if (!ShopActivityRepository::exists(['id' => $request['activity_id']])){
            $this->setError('商品活动记录不存在！');
            return false;
        }
        $upd_arr = [
            'status'        => $request['status'],
            'show_image'    => $request['show_image'],
            'stop_time'     => $request['stop'] == 1 ? 0 : time(),
            'updated_at'    => time()
        ];
        if (!ShopActivityRepository::getUpdId(['id' => $request['activity_id']],$upd_arr)){
            $this->setError('修改失败！');
            return false;
        }
        $this->setMessage('修改成功！');
        return true;
    }

    /**
     * 获取活动商品列表（后台）
     * @param $request
     * @return bool|mixed|null
     */
    public function getActivityGoodsList($request)
    {
        $page           = $request['page'] ?? 1;
        $page_num       = $request['page_num'] ?? 20;
        $type           = $request['type'] ?? null;
        $keywords       = $request['keywords'] ?? null;
        $where          = ['id' => ['>',0]];
        if (!empty($type)){
            $where['type'] = $type;
        }
        if (!empty($keywords)){
            if ($goods_list = ShopGoodsRepository::search([$keywords => ['goods_name']])){
                $goods_ids = array_column($goods_list,'goods_id');
                $where['goods_id'] = ['in',$goods_ids];
            }
        }
        if (!$list = ShopActivityRepository::getList($where,['*'],'created_at','desc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $activity_goods_ids = array_column($list['data'],'goods_id');
        $activity_goods_list = ShopGoodsRepository::getList(['goods_id' => ['in',$activity_goods_ids]],['goods_id','goods_name','goods_price','goods_storage']);
        $show_image_ids = array_column($list['data'],'show_image');
        $show_image_list = CommonImagesRepository::getList(['id' => ['in',$show_image_ids]]);
        foreach ($list['data'] as &$value){
            $value['goods_name']    = '未知';
            $value['goods_price']   = 0;
            $value['goods_storage'] = 0;
            $value['show_image_url'] = '';
            if ($goods = $this->searchArray($activity_goods_list,'goods_id',$value['goods_id'])){
                $value['goods_name']    = reset($goods)['goods_name'];
                $value['goods_price']   = reset($goods)['goods_price'];
                $value['goods_storage'] = reset($goods)['goods_storage'];
            }
            if ($show_image = $this->searchArray($show_image_list,'id',$value['show_image'])){
                $value['show_image_url']    = reset($show_image)['img_url'];
            }
            $value['stop_time']       = !empty($value['stop_time']) ? date('Y-m-d H:m:i',$value['stop_time']):0;
            $value['created_at']      = date('Y-m-d H:m:i',$value['created_at']);
            $value['updated_at']      = date('Y-m-d H:m:i',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            