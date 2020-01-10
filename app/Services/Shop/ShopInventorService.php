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

class ShopInventorService extends BaseService
{
    use HelpTrait;
    /**
     * 创建库存记录
     * @param $request
     * @return bool
     */
    public function createInventor($request)
    {
        $user = Auth::guard('oa_api')->user();
        //余额
        $remain = $this->getCurrentInventor($request['goods_id'],$request['spec_id']);
        if(0==$remain){
            $remain = $this->getCurrentStock($request['goods_id'],$request['spec_id']);
        }
        $remain += $request['amount'] * $request['change_type'];
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
        $data['real_inventor'] = $new_data['remain'] ;
        if(false === $this->updateInventor($where,$data)){
            DB::rollBack();
            Loggy::write('error','updateInventor::库存记录添加失败！',$new_data);
            $this->setError('updateInventor::库存记录添加失败！');
            return false;
        }
        DB::commit();
        $this->setMessage('库存记录添加成功！');
        return true;
    }

    /**
     * @desc 私有函数，更新商品或SKU的库存
     * @param $where
     * @param $data
     * @return bool
     */
    private function updateInventor($where,$data){
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
        $inventory_list = ShopInventoryRepository::getAllList(
            ['goods_id'=>$goods_id, 'spec_id'=>$sepc_id],
            ['remain'], //'id','goods_id','spec_id',
            ['id'],
            ['desc']);
        if(isset($inventory_list['data']['0'])){
            return $inventory_list['data']['0']['remain'];
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
        $inventory_list = ShopGoodsSpecViewRepository::getAllList(
            ['goods_id'=>$goods_id, 'spec_id'=>$sepc_id],
            ['goods_inventor','spec_inventor','goods_stock','spec_stock'], //'id','goods_id','spec_id',
            ['goods_id','spec_id'],
            ['asc','asc']);
        $inventor_column = 'goods_inventor';
        $stock_column = 'goods_stock';
        if(0 == $sepc_id){
            $inventor_column = 'spec_inventor';
            $stock_column = 'spec_stock';
        }
        if(isset($inventory_list['data']['0'])){
            $data = $inventory_list['data']['0'];
            return (0 !== $data[$inventor_column])?$data[$inventor_column]:$data[$stock_column];
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
            function ($src_item, $set_items){
                $src_item ['props'] = '';
                $src_item['inventor'] = 0;
                foreach($set_items as $set_item) {
                    $src_item['props'] .= $set_item['spec_name'] . "：" . $set_item['spec_value'] . "；";
                }
                $src_item['inventor'] = $this->retriveInventor($src_item);
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
    private function retriveInventor($src_item){
        $inventor_col = 'goods_inventor';
        $stock_col = 'goods_stock';
        if(0 == $src_item['spec_id']){
            $inventor_col = 'spec_inventor';
            $stock_col = 'spec_stock';
        }
        $remain = $src_item[$inventor_col];
        if(0==$src_item[$inventor_col]){
            $remain = $src_item[$stock_col];
        }
        return $remain|0;
    }

}