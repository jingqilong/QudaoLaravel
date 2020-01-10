<?php
namespace App\Services\Shop;

use App\Repositories\ShopGoodsCategoryRepository;
use App\Repositories\ShopGoodsRepository;
use App\Repositories\ShopGoodsSpecRelateRepository;
use App\Repositories\ShopGoodsSpecRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tolawho\Loggy\Facades\Loggy;
use App\Repositories\ShopInventoryRepository;
use App\Repositories\ShopGoodsSpecViewRepository;
use App\Traits\HelpTrait;
use App\Enums\ShopInventorChangeEnum;

class ShopInventorService extends BaseService
{
    use HelpTrait;
    /**
     * 创建库存记录,专门提供给库存调整所用的接口
     * @param $request
     * @return bool
     */
    public function createInventor($request)
    {
        $user = Auth::guard('oa_api')->user();
        //余额
        $old_remain = $this->getCurrentInventor($request['goods_id'],$request['spec_id']);
        $old_stock = $this->getCurrentStock($request['goods_id'],$request['spec_id']);
        if(0==$old_remain){ //假如没有初始化的库存流水，则用可销售库存代替实际库存
            $old_remain = $old_stock;
        }
        $request['change_from'] = ShopInventorChangeEnum::ADJUSTMENT;
        //前端传来的不是加多少，减多少，而是最终是多少。
        $remain = $request['amount'] * $request['change_type'];
        //所以，这里改为
        $request['amount'] =  $remain - $old_remain;
        $request['entry_id'] = $request['entry_id']?? 0;
        //数据
        $new_data = Arr::only($request,['entry_id','goods_id','spec_id','change_type','change_from','amount']);
        $new_data ['remain'] = $remain;
        $new_data ['created_at'] = time();
        $new_data ['created_by'] = (!empty($user))?$user->id:0;

        DB::beginTransaction();

        if (!ShopInventoryRepository::getAddId($new_data)){
            DB::rollBack();
            Loggy::write('error','ShopInventoryRepository::库存记录添加失败！',$new_data);
            $this->setError('ShopInventoryRepository::库存记录添加失败！');
            return false;
        }
        $where = Arr::only($new_data,['goods_id','spec_id']);
        //同时更新 商品 或 SKU表中的 real_inventor stock
        $data['real_inventor'] = $new_data['remain'] ;
        $data['stock'] =  $old_stock + $request['amount'] ;
        if(false === $this->updateInventorField($where,$data)){
            DB::rollBack();
            Loggy::write('error','updateInventorField::库存记录添加失败！',$new_data);
            $this->setError('updateInventorField::库存记录添加失败！');
            return false;
        }
        DB::commit();
        $this->setMessage('库存记录添加成功！');
        return true;
    }

    /**
     * @desc 私有函数，更新商品或SKU的记录中的real_inventor和stock字段
     * @param $where
     * @param $data
     * @return bool
     */
    private function updateInventorField($where,$data){
        if(0 == $where['spec_id']){
            unset($where['spec_id']);
            return ShopGoodsRepository::update(['id'=>$where['goods_id']],$data);
        }
        return ShopGoodsSpecRelateRepository::update(['id' =>$where['spec_id'], 'goods_id'=> $where['goods_id']],$data);

    }

    /**
     * @desc 获取当前库存
     * @param $goods_id
     * @param int $sepc_id
     * @return int
     */
    public function getCurrentInventor($goods_id,$sepc_id=0){
        $inventory_list = ShopGoodsSpecViewRepository::getAllList(
            ['goods_id'=>$goods_id, 'spec_id'=>$sepc_id],
            ['goods_inventor','spec_inventor'], //'id','goods_id','spec_id',
            ['goods_id','spec_id'],
            ['asc','asc']);
        $inventor_col = 'spec_inventor';
        if(0 == $sepc_id + 0 ){
            $inventor_col = 'goods_inventor';
        }
        if(isset($inventory_list['data']['0'])){
            $data = $inventory_list['data']['0'];
            return (0 !== $data[$inventor_col])?$data[$inventor_col]:0;
        }
        return 0;
    }

    /**
     * @desc 获取当前库存(从SPU或SKU中获取数据)
     * @param $goods_id
     * @param int $sepc_id
     * @return int
     */
    public function getCurrentStock($goods_id,$sepc_id=0){
        $stock_list = ShopGoodsSpecViewRepository::getAllList(
            ['goods_id'=>$goods_id, 'spec_id'=>$sepc_id],
            ['goods_stock','spec_stock'], //'id','goods_id','spec_id',
            ['goods_id','spec_id'],
            ['asc','asc']);
        $stock_col = 'spec_stock';
        if(0 == $sepc_id + 0 ){
            $stock_col = 'goods_stock';
        }
        if(isset($stock_list['data']['0'])){
            $data = $stock_list['data']['0'];
            return (0 !== $data[$stock_col])?$data[$stock_col]:0;
        }
        return 0;
    }

    /**
     * @param $request
     * @return bool|mixed|null
     */
    public function getInventorList($request){
        $page = $request['page'] ?? 1;
        $page_num = $request['page_num'] ?? 20;
        $where = Arr::only($request,['goods_id', 'spec_id']);
        foreach ($where as $key => $value) {
            if(empty($value)){
                unset($where[$key]);
            }
        }
        if(isset($request['name'])){
            $where['name'] = ['like',$request['name']];
        }
        $columns = ['goods_id','spec_id','name','spec_ids' ,'category' ,'goods_stock','spec_stock','spec_inventor','goods_inventor','image_ids'];
        if (!$list = ShopGoodsSpecViewRepository::getList($where,$columns,['goods_id','spec_id'],['desc','desc'],$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list = $this->removePagingField($list);
        //处理分类：
        if(!$list['data'] = ShopGoodsCategoryRepository::bulkHasOneSet($list['data'],['from'=>'category','to'=>'id'],['id'=>'category_id','name'=>'category_name'])){
            $this->setError('获取失败！');
            return false;
        }
        //处理规格属性
        $list['data'] = ShopGoodsSpecRepository::bulkHasManyWalk(
            $list['data'],
            ['from'=>'spec_ids','to'=>'id'],
            ['id','spec_name','spec_value'],
            [],
            function ($src_item, $shop_goods_spec_items){
                $src_item ['props'] = '';
                $src_item['amount'] = 0;
                $src_item['spec_id'] |= 0;
                foreach($shop_goods_spec_items as $set_item) {
                    $src_item['props'] .= $set_item['spec_name'] . "：" . $set_item['spec_value'] . "；";
                }
                //$src_item['change_from_title'] = ShopInventorChangeEnum::getLabels($src_item['change_from']);
                $src_item['amount'] = $this->retrieveInventor($src_item);
                unset($src_item['spec_ids'],$src_item['spec_inventor'],$src_item['goods_inventor'],$src_item['spec_stock'],$src_item['goods_stock']);
                return $src_item;
            });
        //处理图像,获取图片
        $list['data']  = ImagesService::getListImages($list['data'],['image_ids' => 'single']);
        $this->setMessage('获取成功！');
        return $list;

    }

    /**
     * 因为，添加商品时未初始化库存，现在仅从商品或SKU表获取数据。
     * @param $src_item
     * @return mixed
     */
    private function retrieveInventor($src_item){
        $inventor_col = 'spec_inventor';
        $stock_col = 'spec_stock';
        if(0 == $src_item['spec_id'] + 0 ){
            $inventor_col = 'goods_inventor';
            $stock_col = 'goods_stock';
        }
        $remain = $src_item[$inventor_col] + 0;
        if(0 == $remain){
            $remain = $src_item[$stock_col] + 0;
        }
        return $remain|0;
    }

    /**
     * 锁定库存
     * @param $goods_id
     * @param int $spec_id
     * @param int $amount
     * @return bool
     */
    public function lockStock($goods_id,$spec_id = 0, $amount = 1){
        return true;
    }

    /**
     * 解锁库存
     * @param $goods_id
     * @param int $spec_id
     * @param int $amount
     * @return bool
     */
    public function unlockStock($goods_id,$spec_id = 0, $amount = 1){
        return true;
    }

    /**
     * @desc 统一更新库存接口，添加库存台帐变更流水
     * @param $entry_id
     * @param $goods_id
     * @param $spec_id
     * @param $change_type
     * @param $amount
     * @return bool
     */
    public function updateInventor($entry_id,$goods_id,$spec_id,$amount,$change_type,$change_from=2){
        //余额
        $old_remain = $this->getCurrentInventor($goods_id,$spec_id);
        if(0==$old_remain){
            $old_remain = $this->getCurrentStock($goods_id,$spec_id);
        }
        $new_data['entry_id'] = $entry_id;
        $new_data['goods_id'] = $goods_id;
        $new_data['spec_id'] = $spec_id;
        $new_data['amount'] = $change_type * $amount;
        $new_data['remain'] = $old_remain + $new_data['amount'];
        $request['change_from'] = $change_from;
        $new_data ['created_at'] = time();
        $new_data ['created_by'] = 0;
        DB::beginTransaction();

        if (!ShopInventoryRepository::getAddId($new_data)){
            DB::rollBack();
            Loggy::write('error','ShopInventoryRepository::库存记录添加失败！',$new_data);
            $this->setError('ShopInventoryRepository::库存记录添加失败！');
            return false;
        }
        $where = Arr::only($new_data,['goods_id','spec_id']);
        //同时更新 商品 或 SKU表中的 real_inventor
        $data['real_inventor'] = $new_data['remain'] ;
        if(false === $this->updateInventorField($where,$data)){
            DB::rollBack();
            Loggy::write('error','updateInventorField::库存记录添加失败！',$new_data);
            $this->setError('updateInventorField::库存记录添加失败！');
            return false;
        }
        DB::commit();
        $this->setMessage('库存记录添加成功！');
        return true;
    }


}