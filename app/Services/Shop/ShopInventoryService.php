<?php
namespace App\Services\Shop;

use App\Services\BaseService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tolawho\Loggy\Facades\Loggy;
use App\Repositories\ShopInventoryRepository;

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
        $remain +=  $request['amount'] * $request['change_type'];
        //数据
        $new_data = Arr::only($request,['entry_id','goods_id','spec_id','change_type','change_from','amount']);
        $new_data ['remain'] = $remain;
        $new_data ['created_at'] = time();
        $new_data ['created_by'] = (!empty($user))?$user->id:0;
        if (!ShopInventoryRepository::getAddId(['content' => $request['content'],'created_at' => time()])){
            $this->setError('添加失败！');
            return false;
        }
        $this->setMessage('添加成功！');
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
            ['id','goods_id','spec_id','remain'],
            ['id'],
            ['desc'],
            1,
            1);
        if($inventory){
            return $inventory['data']['remain'];
        }
        return 0;
    }

}