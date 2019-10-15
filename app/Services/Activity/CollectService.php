<?php
namespace App\Services\Activity;


use App\Repositories\ActivityCollectRepository;
use App\Repositories\ActivityDetailRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;

class CollectService extends BaseService
{
    public $auth;

    /**
     * CollectService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }

    /**
     * 收藏活动或取消收藏
     * @param $activity_id
     * @return bool
     */
    public function is_collect($activity_id)
    {
        if (!ActivityDetailRepository::exists(['id' => $activity_id])){
            $this->setError('活动不存在！');
            return false;
        }
        $member = $this->auth->user();
        $add_arr = [
            'activity_id' => $activity_id,
            'member_id'   => $member->m_id,
        ];
        if ($id = ActivityCollectRepository::getField(array_merge($add_arr,['deleted_at' => 0]),'id')){
            $add_arr['deleted_at'] = time();
            if (!ActivityCollectRepository::getUpdId(['id' => $id],$add_arr)){
                $this->setError('取消失败！');
                return false;
            }
            $this->setMessage('取消成功！');
            return true;
        }
        if ($id = ActivityCollectRepository::getField(array_merge($add_arr,['deleted_at' => ['>', 0]]),'id')){
            $add_arr['deleted_at'] = 0;
            if (!ActivityCollectRepository::getUpdId(['id' => $id],$add_arr)){
                $this->setError('收藏失败！');
                return false;
            }
            $this->setMessage('收藏成功！');
            return true;
        }
        $add_arr['created_at'] = time();
        if (!ActivityCollectRepository::getAddId($add_arr)){
            $this->setError('收藏失败！');
            return false;
        }
        $this->setMessage('收藏成功！');
        return true;
    }
}
            