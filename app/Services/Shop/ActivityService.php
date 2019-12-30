<?php
namespace App\Services\Shop;


use App\Enums\ShopActivityEnum;
use App\Repositories\CommonImagesRepository;
use App\Repositories\ShopActivityRepository;
use App\Repositories\ShopActivityViewRepository;
use App\Repositories\ShopGoodsCommonRepository;
use App\Repositories\ShopGoodsRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\DB;

class ActivityService extends BaseService
{
    use HelpTrait;
    /**
     * 获取首页积分展示商品
     * @return array
     */
    public static function getHomeShow(){
        if (!$activity_goods = ShopActivityRepository::getOne(['type' => ShopActivityEnum::HOME_SHOW,'status' => ShopActivityEnum::OPEN])){
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
        $where  = ['type' => ShopActivityEnum::GOOD_RECOMMEND,'status' => ShopActivityEnum::OPEN,'deleted_at' => 0];
        $column = ['goods_id','name','price','banner_ids','labels'];
        if (!$activity_goods = ShopActivityViewRepository::getList($where,$column)){
//            self::setMessage('没有活动商品！');
            return [];
        }
        $activity_goods = ImagesService::getListImages($activity_goods,['banner_ids' => 'single']);
        foreach ($activity_goods as &$goods){
            $goods['price']  = empty($goods['price']) ? 0 : round($goods['price'] / 100,2);
            $goods['labels'] = empty($goods['labels']) ? [] : explode(',',trim($goods['labels'],','));
        }
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
            'show_image'    => $request['show_image'] ?? 0,
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
            'show_image'    => $request['show_image'] ?? 0,
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
        $column = ['id','name','price','type','show_image','status','created_at','updated_at'];
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
            $value['status_name']     = ShopActivityEnum::getStatus($value['status']);
            $value['type_name']       = ShopActivityEnum::getType($value['type']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 商品删除数据前的检查
     * @param $goods_id
     * @return bool
     */
    public function deleteBeforeCheck($goods_id){
        if ($banner_list = ShopActivityRepository::getList(['goods_id' => $goods_id])){
            foreach ($banner_list as $value){
                if (ShopActivityEnum::SCORE_EXCHANGE == $value['type']){
                    continue;
                }
                $this->setError(ShopActivityEnum::getType($value['type']) . '活动商品，无法删除！');
                return false;
            }
        }
        $this->setMessage('当前商品不在活动列表');
        return true;
    }

    /**
     * 删除活动商品
     * @param $activity_id
     * @return bool
     */
    public function deleteActivityGoods($activity_id)
    {
        if (!ShopActivityRepository::exists(['id' => $activity_id])){
            $this->setError('商品活动记录不存在！');
            return false;
        }
        if (!ShopActivityRepository::delete(['id' => $activity_id])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 设置活动商品状态
     * @param $request
     * @return bool
     */
    public function setActivityGoodsStatus($request)
    {
        if (!$activity_goods = ShopActivityRepository::getOne(['id' => $request['activity_id']])){
            $this->setError('商品活动记录不存在！');
            return false;
        }
        //如果是首页展示，则只能存在一个展示商品
        DB::beginTransaction();
        if ($activity_goods['type'] == ShopActivityEnum::HOME_SHOW){
            if ($request['status'] == ShopActivityEnum::DISABLE){
                $this->setError('首页展示商品不能关闭，只能开启或添加另一个，此记录将被关闭！');
                DB::rollBack();
                return false;
            }
            if ($request['status'] == ShopActivityEnum::OPEN){
                $where = ['type' => ShopActivityEnum::HOME_SHOW,'status' => ShopActivityEnum::OPEN];
                $upd   = ['status' => ShopActivityEnum::DISABLE,'updated_at' => time()];
                if (ShopActivityRepository::exists($where)){
                    if (!ShopActivityRepository::update($where,$upd)){
                        $this->setError('修改失败！');
                        DB::rollBack();
                        return false;
                    }
                }
            }
        }

        $upd_arr = [
            'status'        => $request['status'],
            'updated_at'    => time()
        ];

        if (!ShopActivityRepository::getUpdId(['id' => $request['activity_id']],$upd_arr)){
            $this->setError('修改失败！');
            DB::rollBack();
            return false;
        }
        DB::commit();
        $this->setMessage('修改成功！');
        return true;
    }

    /**
     * 添加或更新“积分兑换”栏目
     * @param $goods_id
     * @param int $status
     * @return bool
     */
    public function addOrUpdScoreExchange($goods_id, $status = ShopActivityEnum::OPEN){
        if ($activity_goods = ShopActivityRepository::getOne(['goods_id' => $goods_id,'type' => ShopActivityEnum::SCORE_EXCHANGE])){
            $id = $activity_goods['id'];
        }else{
            $add_arr = ['goods_id' => $goods_id,'type' => ShopActivityEnum::SCORE_EXCHANGE,'status' => $status,'created_at' => time(),'updated_at' => time()];
            if (!$id = ShopActivityRepository::getAddId($add_arr)){
                $this->setError('添加商品至"积分兑换"栏目失败！');
                return false;
            }
        }
        $upd_arr = ['status' => $status,'updated_at' => time()];
        if (!ShopActivityRepository::getUpdId(['id' => $id],$upd_arr)){
            $this->setError('更新“积分兑换”栏目失败！');
            return false;
        }
        $this->setMessage('操作成功！');
        return true;
    }
}
            