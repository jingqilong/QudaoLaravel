<?php
namespace App\Services\Shop;


use App\Repositories\ShopCartRepository;
use App\Repositories\ShopGoodsRepository;
use App\Repositories\ShopGoodsSpecRelateRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class CartService extends BaseService
{
    use HelpTrait;
    public $auth;
    /**
     * CartService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }

    /**
     * 商品添加购物车
     * @param $request
     * @return bool
     */
    public function addShopCar($request)
    {
        $memberInfo     = $this->auth->user();
        $member_id      = $memberInfo->m_id;
        if (!ShopGoodsRepository::exists(['id' => $request['goods_id'],'deleted_at' => 0])){
            $this->setError('无效的商品!');
            return false;
        }
        if (!ShopGoodsSpecRelateRepository::exists(['id' => $request['spec_relate_id'],'deleted_at' => 0])){
            $this->setError('无效的商品规格!');
            return false;
        }
        $add_arr = [
            'member_id'         => $member_id,
            'goods_id'          => $request['goods_id'],
            'spec_relate_id'    => $request['spec_relate_id'],
        ];
        if (ShopCartRepository::exists($add_arr)){
            $this->setError('商品已添加至购物车!');
            return false;
        }
        $add_arr['number']     = $request['number'];
        $add_arr['created_at'] = $add_arr['updated_at'] =time();
        if (!ShopCartRepository::getAddId($add_arr)){
            $this->setError('添加失败!');
            return false;
        }
        $this->setMessage('添加成功!');
        return true;
    }

    /**
     * 删除购物车商品
     * @param $request
     * @return bool
     */
    public function delShopCar($request)
    {
        if (!ShopCartRepository::getOne(['id' => $request['id']])){
            $this->setError('商品不存在!');
            return false;
        }
        if (!ShopCartRepository::deleted(['id' => $request['id']])){
            $this->setError('删除失败!');
            return false;
        }
        $this->setMessage('删除成功!');
        return true;
    }

    /**
     * 用户编辑购物车商品数量
     * @param $request
     * @return bool
     */
    public function changeCarNum($request)
    {
        if (!$car_goods = ShopCartRepository::getOne(['id' => $request['id']])){
            $this->setError('购物车无此记录!');
            return false;
        }
        $spec_relate_id = $request['spec_relate_id'] ?? null;
        if (!empty($spec_relate_id) && !ShopGoodsSpecRelateRepository::exists(['id' => $spec_relate_id])){
            $this->setError('无效的商品规格!');
            return false;
        }
        if ($request['change'] == '-' && $car_goods['number'] == 1){
            $this->setError('该商品不能再少了哦!');
            return false;
        }
        if ($request['change'] == '+'){
            $upd_arr = ['updated_at' => time(), 'number' => ++$car_goods['number']];

        }else{
            $upd_arr = ['updated_at' => time(), 'number' => --$car_goods['number']];
        }
        if (!empty($spec_relate_id)){
            $upd_arr['spec_relate_id'] = $spec_relate_id;
        }
        if (!ShopCartRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('修改失败了诶!');
            return false;
        }
        if (!$res = ShopCartRepository::getOne(['id' => $request['id']],['number'])){
            $this->setError('网络正在小差');
            return false;
        }
        $this->setMessage('修改成功!');
        return $res;
    }

    public function shopCarList($request)
    {
        $memberInfo = $this->auth->user();
        $member_id  = $memberInfo->m_id;
        $page         = $request['page'] ?? 1;
        $page_num     = $request['page_num'] ?? 20;
        $where        = ['member_id' => $member_id];
        if (!$list = ShopCartRepository::getList($where,['*'],'id','desc',$page,$page_num)){
            $this->setError('获取失败!');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据!');
            return $list;
        }
        $goods_ids          = array_column($list['data'],'goods_id');
        $spec_relate_ids    = array_column($list['data'],'spec_relate_id');
    }
}
            