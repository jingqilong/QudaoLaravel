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

class ShopInventorService extends BaseService
{
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
        $remain += $request['amount'] * $request['change_type'];
        //数据
        $new_data = Arr::only($request,['entry_id','goods_id','spec_id','change_type','change_from','amount']);
        $new_data ['remain'] = $remain;
        $new_data ['created_at'] = time();
        $new_data ['created_by'] = (!empty($user))?$user->id:0;
        DB::beginTransaction();
        if (!ShopInventoryRepository::getAddId($new_data)){
            DB::rollBack();
            Loggy::write('error','库存记录添加失败！',$new_data);
            $this->setError('库存记录添加失败！');
            return false;
        }
        $where = Arr::only($new_data,['goods_id','spec_id']);
        $data['real_inventor'] = $new_data['remain'] ;
        if(!$this->updateInventor($where,$data)){
            DB::rollBack();
            Loggy::write('error','库存记录添加失败！',$new_data);
            $this->setError('库存记录添加失败！');
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
        return ShopGoodsSpecRelateRepository::update($where,$data);

    }

    /**
     * @desc 获取当前库存
     * @param $goods_id
     * @param int $sepc_id
     * @return int
     */
    public function getCurrentInventor($goods_id,$sepc_id=0){
        $inventory = ShopInventoryRepository::getList(
            [['goods_id'=>$goods_id], ['spec_id'=>$sepc_id]],
            ['remain'], //'id','goods_id','spec_id',
            ['id'],
            ['desc'],
            1,
            1);
        if(isset($inventory['data']['0'])){
            return $inventory['data']['0']['remain'];
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
        if(isset($request['name'])){
            $where['name'] = ['like',$request['name']];
        }
        $column = ['goods_id','spec_id','name','spec_ids' ,'category' ,'spec_inventor','goods_inventor','image_ids'];
        if (!$list = ShopGoodsSpecViewRepository::getList($where,$column,['goods_id','spec_id'],['desc','desc'],$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        //处理分类：
        if(!$list['data'] = ShopGoodsCategoryRepository::bulkHasOneSet($list['data'],['from'=>'category','to'=>'id'],['id'=>'category_id','name'=>'category_name'])){
            $this->setError('获取失败！');
            return false;
        }
        //处理规格属性
        if(!$attrs = ShopGoodsSpecRepository::getHasManyList($list['data'],[],['from'=>'spec_ids','to'=>'id'],['id','spec_name','spec_value'])){
            $this->setError('获取失败！');
            return false;
        }
        foreach($list['data'] as  & $item){
            $keys= explode(',',trim($item['spec_ids'],','));
            $item ['props'] = '';
            foreach($keys as $key){
                $item ['props'] .= $attrs[$key]['spec_name'] ."：".  $attrs[$key]['spec_value'] ."；";
            }
        }
        //处理图像,获取图片
        $list['data']  = ImagesService::getListImages($list['data'],['image_ids' => 'single']);
        $this->setMessage('获取成功！');
        return $list;

    }

}