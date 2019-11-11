<?php
namespace App\Services\Member;


use App\Services\BaseService;
use App\Enums\CollectTypeEnum;
use App\Repositories\ActivityDetailRepository;
use App\Repositories\HouseDetailsRepository;
use App\Repositories\MemberCollectRepository;
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
     * 公共收藏
     * @param $type
     * @param $target_id
     * @return bool
     */
    public function is_collect($type, $target_id)
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
                $this->setError('暂未开发！');
                return false;
                break;
            default:
                $this->setError('暂无此收藏类别！');
                return false;
                break;
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
}
            