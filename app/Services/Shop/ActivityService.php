<?php
namespace App\Services\Shop;


use App\Repositories\CommonImagesRepository;
use App\Repositories\ShopActivityRepository;
use App\Repositories\ShopActivityViewRepository;
use App\Repositories\ShopGoodsCommonRepository;
use App\Repositories\ShopGoodsRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
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
        if (!$activity_goods = ShopActivityViewRepository::getList(['type' => 2,'status' => 2,'deleted_at' => 0],['goods_id','name','price','banner_ids'])){
//            self::setMessage('没有活动商品！');
            return [];
        }
        $activity_goods = ImagesService::getListImages($activity_goods,['banner_ids' => 'single']);
//        self::setMessage('获取成功！');
        return $activity_goods;
    }

    /**
     * 添加活动商品
     * @param $request
     * @return bool
     */
    public function addActivityGoods($request)
    {
        if (!ShopGoodsRepository::exists(['id' => $request['goods_id']])){
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
        $order          = 'created_at';
        $desc_asc       = 'desc';
        if (!empty($type)){
            $where['type'] = $type;
        }
        $column = ['id','name','price','type','show_image','stop_time','status','created_at','updated_at'];
        if (!empty($keywords)){
            if (!$list = ShopActivityViewRepository::search([$keywords => ['name']],$where,$column,$page,$page_num,$order,$desc_asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = ShopActivityViewRepository::getList($where,$column,$order,$desc_asc,$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }

        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data'] = ImagesService::getListImages($list['data'],['show_image' => 'single']);
        foreach ($list['data'] as &$value){
            $value['stop_time']       = !empty($value['stop_time']) ? date('Y-m-d H:m:i',$value['stop_time']) : 0;
            $value['status_name']     = $value['status'] == 1 ? '禁用' : '展示';
            $value['type_name']       = $value['type'] == 1 ? '积分兑换' : '好物推荐';
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            