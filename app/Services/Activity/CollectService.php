<?php
namespace App\Services\Activity;


use App\Enums\CollectTypeEnum;
use App\Repositories\ActivityCollectRepository;
use App\Repositories\ActivityDetailRepository;
use App\Repositories\MemberCollectRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class CollectService extends BaseService
{
    use HelpTrait;
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
            'member_id'   => $member->id,
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

    /**
     * 获取收藏列表（前端）
     * @param $request
     * @return mixed
     */
    public function collectList($request)
    {
        $type       = $request['type'];
        $member     = $this->auth->user();
        $where      = ['type' => CollectTypeEnum::ACTIVITY,'member_id' => $member->id,'deleted_at' => 0];
        $activity_where = ['created_at' => ['<>',0]];
        $time = time();
        switch ($type){
            case 1:
                break;
            case 2:
                $activity_where['start_time'] = ['>',$time];
                break;
            case 3:
                $activity_where['start_time'] = ['<',$time];
                $activity_where['end_time']   = ['>',$time];
                break;
            case 4:
                $activity_where['end_time']   = ['<',$time];
                break;
        }
        if (!$list = MemberCollectRepository::getList($where)){
            $this->setMessage('暂无数据！');
            list($page,$page_num) = $this->inputPage();
            return ['current_page' => $page,'data' => [],'last_page' => $page,'page_num' => $page_num,'total' => 0];
        }
        $activity_ids = array_column($list['data'],'target_id');
        $activity_column = ['id','name','address','price','start_time','end_time','cover_id','theme_id','stop_selling'];
        if (!$activities = ActivityDetailRepository::getActivityList(array_merge($activity_where,['id' => ['in',$activity_ids]]),$activity_column,'start_time','desc')){
            $this->setError('获取失败！');
            return false;
        }
        $this->setMessage('获取成功！');
        return $activities;
    }
}
            