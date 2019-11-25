<?php
namespace App\Services\Member;


use App\Repositories\PrimeMerchantRepository;
use App\Repositories\ShopCartRepository;
use App\Repositories\ShopGoodsRepository;
use App\Services\BaseService;
use App\Enums\CollectTypeEnum;
use App\Repositories\ActivityDetailRepository;
use App\Repositories\HouseDetailsRepository;
use App\Repositories\MemberCollectRepository;
use App\Services\Shop\CartService;
use App\Services\Shop\GoodsSpecRelateService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class CollectService extends BaseService
{
    use HelpTrait;
    protected $auth;

    /**
     * MemberService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }

    /**
     * 检验收藏是否存在
     * @param $type
     * @param $target_id
     * @return bool
     */
    public function collectType($type, $target_id)
    {
        switch ($type){
            case CollectTypeEnum::ACTIVITY:
                if (!ActivityDetailRepository::exists(['id' => $target_id])){
                    $this->setError('活动不存在！');
                    return false;
                }
                break;
            case CollectTypeEnum::SHOP:
                $this->setError('暂未开发！');
                return false;
                break;
            case CollectTypeEnum::HOUSE:
                if (!HouseDetailsRepository::exists(['id' => $target_id])){
                    $this->setError('房源不存在！');
                    return false;
                }
                break;
            case CollectTypeEnum::PRIME:
                if (!PrimeMerchantRepository::exists(['id' => $target_id])){
                    $this->setError('商家不存在！');
                    return false;
                }
                break;
            default:
                $this->setError('暂无此收藏类别！');
                return false;
                break;
        }
    }


    /**
     * 公共收藏
     * @param $type
     * @param $target_id
     * @return bool
     */
    public function isCollect($type, $target_id)
    {
        if (!$this->collectType($type, $target_id)){
            return false;
        }
        $member = $this->auth->user();
        $add_arr = [
            'type'          => $type,
            'target_id'     => $target_id,
            'member_id'     => $member->m_id,
        ];
        if ($id = MemberCollectRepository::getField(array_merge($add_arr,['deleted_at' => 0]),'id')){
            $add_arr['deleted_at'] = time();
            if (!MemberCollectRepository::getUpdId(['id' => $id],$add_arr)){
                $this->setError('取消失败！');
                return false;
            }
            $this->setMessage('取消成功！');
            return true;
        }
        if ($id = MemberCollectRepository::getField(array_merge($add_arr,['deleted_at' => ['>', 0]]),'id')){
            $add_arr['deleted_at'] = 0;
            if (!MemberCollectRepository::getUpdId(['id' => $id],$add_arr)){
                $this->setError('收藏失败！');
                return false;
            }
            $this->setMessage('收藏成功！');
            return true;
        }
        $add_arr['created_at'] = time();
        if (!MemberCollectRepository::getAddId($add_arr)){
            $this->setError('收藏失败！');
            return false;
        }
        $this->setMessage('收藏成功！');
        return true;
    }

    /**
     * 收藏列表
     * @param $request
     * @return array|bool|mixed|null
     */
    public function collectList($request)
    {
        if (empty($type = CollectTypeEnum::getType($request['type']))){
            $this->setError('暂无此收藏类别');
            return false;
        }
        $member     = $this->auth->user();
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 999;
        $where = ['type' => $request['type'],'member_id' => $member->m_id,'deleted_at' => 0];
        if (!$collect_list = MemberCollectRepository::getList($where,['*'],'id','desc',$page,$page_num)){
            $this->setError('获取失败!');
            return false;
        }
        $collect_ids = array_column($collect_list['data'],'target_id');
        $request['collect_ids'] = $collect_ids;
        $collect_list = $this->removePagingField($collect_list);
        switch ($request['type']){
            case CollectTypeEnum::ACTIVITY:
                $collect_list['data'] = ActivityDetailRepository::getCollectList($collect_ids);
                break;
            case CollectTypeEnum::SHOP:
                $collect_list['data'] = ShopGoodsRepository::getCollectList($collect_ids);
                break;
            case CollectTypeEnum::HOUSE:
                $collect_list['data'] = HouseDetailsRepository::getCollectList($collect_ids);
                break;
            case CollectTypeEnum::PRIME:
                $collect_list['data'] = PrimeMerchantRepository::getCollectList($collect_ids);
                break;
            default:
                $this->setError('暂无此收藏类别！');
                return false;
                break;
        }
        $this->setMessage('获取成功!');
        return $collect_list['data'];
    }
}
            