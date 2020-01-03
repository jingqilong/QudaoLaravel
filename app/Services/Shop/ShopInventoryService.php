<?php
namespace App\Services\Shop;

use App\Services\BaseService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tolawho\Loggy\Facades\Loggy;
use App\Repositories\ShopInventoryRepository;
use App\Repositories\ShopGoodsSpecViewRepository;

class ShopInventoryService extends BaseService
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
        $remain = $this->getCurrentInventory($request['goods_id'],$request['spec_id']);
        $remain += $request['amount'] * $request['change_type'];
        //数据
        $new_data = Arr::only($request,['entry_id','goods_id','spec_id','change_type','change_from','amount']);
        $new_data ['remain'] = $remain;
        $new_data ['created_at'] = time();
        $new_data ['created_by'] = (!empty($user))?$user->id:0;
        if (!ShopInventoryRepository::getAddId($new_data)){
            Loggy::write('error','库存记录添加失败！',$new_data);
            $this->setError('库存记录添加失败！');
            return false;
        }
        $this->setMessage('库存记录添加成功！');
        return true;
    }

    /**
     * @desc 获取当前库存
     * @param $goods_id
     * @param int $sepc_id
     * @return int
     */
    public function getCurrentInventory($goods_id,$sepc_id=0){
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
     * @desc 获取商品或SKU的库存列表
     * @param $request
     */
    public function getInventoryList($request){
        $where = Arr::only($request,['entry_id','goods_id','spec_id','page','page_num']);
        $page = $request['page'];
        $page_num = $request['page_num'];
        $list = ShopGoodsSpecViewRepository::getList();

    }

}