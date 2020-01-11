<?php
namespace App\Services\Activity;


use App\Enums\CollectTypeEnum;
use App\Enums\CommonImagesEnum;
use App\Repositories\ActivityDetailRepository;
use App\Repositories\ActivityPastRepository;
use App\Repositories\MemberCollectRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PastService extends BaseService
{


    /**
     * oa 添加往期活动
     * @param $request
     * @return bool
     */
    public function addActivityPast($request)
    {
        //模板
        /*
         * resource_ids:[1,2,...]
         * top:'0' 0不置顶 1置顶
         * hidden:'0' 0隐藏 1显示
         * presentation:''
        */
        $activity_id = $request['activity_id'];
        if (!ActivityDetailRepository::getOne(['id' => $activity_id])){
            $this->setError('活动不存在!');
            return false;
        }
        $parameter   = json_decode($request['parameters'],true);
        $add_arr =[];
        foreach ($parameter as $value){
            if (!isset($value['resource_ids'])){
                $this->setError('资源图片(视频)不能为空!');
                return false;
            }
            if (!isset($value['presentation'])){
                $this->setError('文字描述不能为空！');
                return false;
            }
            $add_arr[] = [
                'activity_id'  => $activity_id,
                'top'          => $value['top'],
                'hidden'       => $value['hidden'],
                'resource_ids' => implode(',',$value['resource_ids']),
                'presentation' => $value['presentation'],
                'created_at'   => time(),
                'updated_at'   => time(),
            ];
        }
        DB::beginTransaction();
        ActivityPastRepository::delete(['activity_id' => $activity_id]);
        if (ActivityPastRepository::exists(['activity_id' => $request['activity_id']])){
            $this->setError('图文(视频)信息已存在!');
            DB::rollBack();
            return false;
        }
        if (!ActivityPastRepository::create($add_arr)){
            $this->setError('添加失败!');
            DB::rollBack();
            return false;
        }
        DB::commit();
        $this->setMessage('添加成功!');
        return true;
    }

    /**
     * 删除往期活动
     * @param $request
     * @return bool
     */
    public function delActivityPast($request)
    {
        if (!ActivityPastRepository::exists(['activity_id' => $request['activity_id']])){
            $this->setError('活动不存在!');
            return false;
        }
        if (!ActivityPastRepository::delete(['id' => $request['id']])){
            $this->setError('删除失败!');
            return false;
        }
        $this->setMessage('删除成功!');
        return true;
    }

    /**
     * OA 修改往期活动
     * @param $request
     * @return bool
     */
    public function editActivityPast($request)
    {
        $activity_id = $request['activity_id'];
        if (!ActivityPastRepository::exists(['activity_id' => $activity_id])){
            $this->setError('往期活动不存在!');
            return false;
        }
        $parameter   = json_decode($request['parameters'],true);
        $upd_arr = [];
        foreach ($parameter as $value){
            if (!isset($value['resource_ids'])){
                $this->setError('资源图片(视频)不能为空!');
                return false;
            }
            if (!isset($value['presentation'])){
                $this->setError('文字描述不能为空！');
                return false;
            }
            $upd_arr = [
                'activity_id'  => $activity_id,
                'top'          => $value['top'],
                'hidden'       => $value['hidden'],
                'resource_ids' => implode(',',$value['resource_ids']),
                'presentation' => $value['presentation'],
                'updated_at'   => time(),
            ];
        }
        DB::beginTransaction();
        ActivityPastRepository::delete(['activity_id' => $activity_id]);
        if (ActivityPastRepository::exists($upd_arr)){
            $this->setError('修改失败!');
            DB::rollBack();
            return false;
        }
        if (!ActivityPastRepository::create($upd_arr)){
            $this->setError('修改失败!');
            DB::rollBack();
            return false;
        }
        DB::commit();
        $this->setMessage('修改成功!');
        return true;
    }

    /**
     * OA 获取往期活动详情
     * @param $request
     * @return bool|mixed|null
     */
    public function getActivityPastList($request)
    {
        $where      = ['activity_id' => $request['activity_id']];
        $column     = ['resource_ids','presentation','hidden','top'];
        if (!$list = ActivityPastRepository::getList($where,$column)){
            $this->setMessage('获取成功');
            return json_encode($list);
        }
        $list = ImagesService::getListImages($list,['resource_ids' => 'several']);
        $this->setMessage('获取成功');
        foreach ($list as &$value){
            $value['resource_ids']  = explode(',',$value['resource_ids']);
        }
        return json_encode($list);
    }
    /**
     * 往期活动
     * @param $request
     * @return array|bool|null
     */
    public function getActivityPast($request)
    {
        $where  = ['activity_id' => $request['id'],'hidden' => 0];
        $column = ['id','resource_ids','top','presentation'];
        $auth = Auth::guard('member_api');
        $member = $auth->user();
        $res['is_collect'] = 0;
        if (MemberCollectRepository::exists(['type' => CollectTypeEnum::ACTIVITY,'target_id' => $request['id'],'member_id' => $member->id,'deleted_at' => 0])){
            $activity['is_collect'] = 1;
        }
        if (!ActivityPastRepository::exists(['activity_id' => $request['id']])) {
            $this->setError('没有此活动!');
            return false;
        }
        $res['banner']      = [];
        $res['video_list']  = [];
        $res['images_list'] = [];
        if (!$list = ActivityPastRepository::getList($where,$column,'top','desc')){
            $this->setError('获取失败');
            return false;
        }
        $list = ImagesService::getListImages($list,['resource_ids' => 'several'],false);
        foreach ($list as $value){
            if ($value['top'] == 1){
                $res['banner'][] = $value;
                continue;
            }
            foreach ($value['resource_urls'] as $item){
                if ($item['file_type'] == CommonImagesEnum::VIDEO){
                    $res['video_list'][] = $value;break;
                }
                if ($item['file_type'] == CommonImagesEnum::IMAGE){
                    $res['images_list'][] = $value;break;
                }
            }
            unset($value['resource_ids'],$value['top']);
        }
        $this->setMessage('获取成功!');
        return $res;
    }
}
            