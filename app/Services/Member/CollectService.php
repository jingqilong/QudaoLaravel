<?php
namespace App\Services\Member;


use App\Enums\CommentsEnum;
use App\Enums\ShopOrderEnum;
use App\Repositories\CommonCommentsRepository;
use App\Repositories\PrimeMerchantRepository;
use App\Repositories\ShopGoodsRepository;
use App\Repositories\ShopOrderRelateRepository;
use App\Services\BaseService;
use App\Enums\CollectTypeEnum;
use App\Repositories\ActivityDetailRepository;
use App\Repositories\HouseDetailsRepository;
use App\Repositories\MemberCollectRepository;
use App\Services\Common\ImagesService;
use App\Services\Shop\OrderRelateService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                if (!ShopGoodsRepository::exists(['id' => $target_id])){
                    $this->setError('商品不存在！');
                    return false;
                }
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
        $this->setMessage('校验通过！');
        return true;
    }


    /**
     * 公共收藏
     * @param $type
     * @param $target_id
     * @return mixed
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
            'member_id'     => $member->id,
        ];
        if ($id = MemberCollectRepository::getField(array_merge($add_arr,['deleted_at' => 0]),'id')){
            $add_arr['deleted_at'] = time();
            if (!MemberCollectRepository::getUpdId(['id' => $id],$add_arr)){
                $this->setError('取消失败！');
                return false;
            }
            $this->setMessage('取消成功！');
            return ['is_collect' => 0];
        }
        if ($id = MemberCollectRepository::getField(array_merge($add_arr,['deleted_at' => ['>', 0]]),'id')){
            $add_arr['deleted_at'] = 0;
            if (!MemberCollectRepository::getUpdId(['id' => $id],$add_arr)){
                $this->setError('收藏失败！');
                return false;
            }
            $this->setMessage('收藏成功！');
            return ['is_collect' => 1];
        }
        $add_arr['created_at'] = time();
        if (!MemberCollectRepository::getAddId($add_arr)){
            $this->setError('收藏失败！');
            return false;
        }
        $this->setMessage('收藏成功！');
        return ['is_collect' => 1];
    }

    /**
     * 收藏列表
     * @param $request
     * @return array|bool|mixed|null
     */
    public function collectList($request)
    {
        if (empty(CollectTypeEnum::getType($request['type']))){
            $this->setError('暂无此收藏类别');
            return false;
        }
        $member     = $this->auth->user();
        $where = ['type' => $request['type'],'member_id' => $member->id,'deleted_at' => 0];
        if (!$collect_list = MemberCollectRepository::getList($where,['*'],'id','desc')){
            $this->setError('获取失败!');
            return false;
        }
        $collect_list = $this->removePagingField($collect_list);
        if (empty($collect_list['data'])){
            $this->setMessage('暂无数据');
            return $collect_list;
        }
        $collect_ids = array_column($collect_list['data'],'target_id');
        $request = [
            'collect_ids'   => $collect_ids,
            'type'          => $request['type'],
        ];
        switch ($request['type']){
            case CollectTypeEnum::ACTIVITY:
                $result = ActivityDetailRepository::getCollectList($request);
                break;
            case CollectTypeEnum::SHOP:
                $result = ShopGoodsRepository::getCollectList($request);
                break;
            case CollectTypeEnum::HOUSE:
                $result = HouseDetailsRepository::getCollectList($request);
                break;
            case CollectTypeEnum::PRIME:
                $result = PrimeMerchantRepository::getCollectList($request);
                break;
            default:
                $this->setError('暂无此收藏类别！');
                return false;
                break;
        }
        $this->setMessage('获取成功!');
        return $result;
    }
}

