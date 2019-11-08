<?php
namespace App\Services\Activity;


use App\Repositories\{
    ActivityCollectRepository,
    ActivityDetailRepository,
    ActivityGuestRepository,
    ActivityHostsRepository,
    ActivityLinksRepository,
    ActivityRegisterRepository,
    ActivityThemeRepository,
    CommonImagesRepository
};
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class DetailService extends BaseService
{
    use HelpTrait;

    /**
     * 添加活动（后台）
     * @param $request
     * @return bool
     */
    public function addActivity($request)
    {
        if (!ActivityThemeRepository::exists(['id' => $request['theme_id']])){
            $this->setError('活动主题不存在！');
            return false;
        }
        $is_recommend = $request['is_recommend'] ?? 0;
        $add_arr = [
            'name'          => $request['name'],
            'address'       => $request['address'],
            'price'         => isset($request['price']) ? $request['price'] * 100 : 0,
            'theme_id'      => $request['theme_id'],
            'start_time'    => strtotime($request['start_time']),
            'end_time'      => strtotime($request['end_time']),
            'is_recommend'  => $is_recommend == 0 ? 0 : time(),
            'cover_id'      => $request['cover_id'],
            'banner_ids'    => $request['banner_ids'],
            'image_ids'     => $request['image_ids'],
            'status'        => $request['status'],
            'firm'          => $request['firm'] ?? '',
            'notice'        => $request['notice'] ?? '',
            'detail'        => $request['detail'] ?? '',
            'is_member'     => $request['is_member'],
        ];
        if (ActivityDetailRepository::exists($add_arr)){
            $this->setError('该活动已添加！');
            return false;
        }
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();

        if ($id = ActivityDetailRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return $id;
        }
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 活动首页列表【有搜索功能】（前端）
     * @param $request
     * @return bool|null
     */
    public function getHomeList($request)
    {
        $page           = $request['page'] ?? 1;
        $page_num       = $request['page_num'] ?? 20;
        $theme_id       = $request['theme_id'] ?? null;
        $is_recommend   = $request['is_recommend'] ?? null;
        $keywords       = $request['keywords'] ?? null;
        $where          = ['status' => 1];
        $order          = 'id';
        $desc_asc       = 'desc';
        if (!empty($theme_id)){
            $where['theme_id']  = $theme_id;
        }
        if (!empty($is_recommend)){
            $where['is_recommend']  = ['>',0];
            $order = 'is_recommend';
        }
        $activity_column = ['id','name','address','price','start_time','end_time','cover_id','theme_id'];
        if (!empty($keywords)){
            $keyword = [$keywords => ['name', 'address', 'price']];
            if (!$list = ActivityDetailRepository::search($keyword,$where,$activity_column,$page,$page_num,$order,$desc_asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = ActivityDetailRepository::getList($where,$activity_column,$order,$desc_asc,$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
       $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $theme_ids  = array_column($list['data'],'theme_id');
        $themes     = ActivityThemeRepository::getList(['id' => ['in',$theme_ids]],['id','name','icon_id']);
        $icon_ids   = array_column($themes,'icon_id');
        $icons      = CommonImagesRepository::getList(['id' => ['in',$icon_ids]]);
        foreach ($list['data'] as &$value){
            $theme = $this->searchArray($themes,'id',$value['theme_id']);
            if ($theme)
                $icon  = $this->searchArray($icons,'id',reset($theme)['icon_id']);
            $value['theme_name'] = $theme ? reset($theme)['name'] : '活动';
            $value['theme_icon'] = $icons ? reset($icon)['img_url'] : '';
            $value['price'] = empty($value['price']) ? '免费' : round($value['price'] / 100,2).'元';
            if ($value['start_time'] > time()){
                $value['status'] = '报名中';
            }
            if ($value['start_time'] < time() && $value['end_time'] > time()){
                $value['status'] = '进行中';
            }
            if ($value['end_time'] < time()){
                $value['status'] = '已结束';
            }
            $start_time    = date('Y年m月d日',$value['start_time']);
            $end_time      = date('m月d日',$value['end_time']);
            $value['activity_time'] = $start_time . '～' . $end_time;
            $value['cover'] = empty($value['cover_id']) ? '':CommonImagesRepository::getField(['id' => $value['cover_id']],'img_url');
            unset($value['theme_id'],$value['start_time'],$value['end_time'],$value['cover_id']);
        }
        $this->setMessage('获取成功！');
        return $list['data'];
    }

    /**
     * 软删除活动（后台）
     * @param $id
     * @return bool
     */
    public function softDeleteActivity($id)
    {
        if (!$activity = ActivityDetailRepository::getOne(['id' => $id])){
            $this->setError('活动不存在！');
            return false;
        }
        if ($activity['deleted_at'] > 0){
            $this->setError('活动已删除！');
            return false;
        }
        if (ActivityRegisterRepository::exists(['activity_id' => $id]) || ActivityGuestRepository::exists(['activity_id' => $id])){
            $this->setError('活动已有人参加，无法进行删除！');
            return false;
        }
        if (ActivityDetailRepository::getUpdId(['id' => $id],['deleted_at' => time()])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }

    /**
     * 编辑活动（后台）
     * @param $request
     * @return bool
     */
    public function editActivity($request)
    {
        if (!$activity = ActivityDetailRepository::getOne(['id' => $request['id']])){
            $this->setError('活动不存在！');
            return false;
        }
        if (!ActivityThemeRepository::exists(['id' => $request['theme_id']])){
            $this->setError('活动主题不存在！');
            return false;
        }
        $is_recommend = $request['is_recommend'] ?? 0;
        $upd_arr = [
            'name'          => $request['name'],
            'address'       => $request['address'],
            'price'         => isset($request['price']) ? $request['price'] * 100 : 0,
            'theme_id'      => $request['theme_id'],
            'start_time'    => strtotime($request['start_time']),
            'end_time'      => strtotime($request['end_time']),
            'is_recommend'  => $is_recommend == 0 ? 0 : time(),
            'cover_id'      => $request['cover_id'],
            'banner_ids'    => $request['banner_ids'],
            'image_ids'     => $request['image_ids'],
            'status'        => $request['status'],
            'firm'          => $request['firm'] ?? '',
            'notice'        => $request['notice'] ?? '',
            'detail'        => $request['detail'] ?? '',
            'is_member'     => $request['is_member'],
        ];
        if (ActivityDetailRepository::exists($upd_arr)){
            $this->setError('该活动已存在！');
            return false;
        }
        $upd_arr['updated_at'] = time();

        if (ActivityDetailRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取活动列表（后台）
     * @param $request
     * @return bool|null
     */
    public function getActivityList($request)
    {
        $page           = $request['page'] ?? 1;
        $page_num       = $request['page_num'] ?? 20;
        $start_time     = $request['start_time'] ?? null;
        $end_time       = $request['end_time'] ?? null;
        $is_recommend   = $request['is_recommend'] ?? null;
        $status         = $request['status'] ?? null;
        $is_member      = $request['is_member'] ?? null;
        $keywords       = $request['keywords'] ?? null;
        $where          = ['id' => ['>',0]];
        if (!empty($start_time)){
            $where['start_time']    = ['>',strtotime($start_time)];
        }
        if (!empty($end_time)){
            $where['end_time']      = ['<',strtotime($end_time)];
        }
        if ($is_recommend != null){
            $where['is_recommend']  = $is_recommend;
        }
        if (!empty($status)){
            $where['status']  = $status;
        }
        if (!empty($is_member)){
            $where['is_member']  = $is_member;
        }
        $activity_column = ['id','name','address','price','start_time','end_time','is_recommend','status','theme_id','firm','is_member','created_at','updated_at','deleted_at'];
        if (!empty($keywords)){
            if ($search_themes = ActivityThemeRepository::getList(['name' => $keywords],['id'])){
                $theme_ids = array_column($search_themes,'id');
                $where['theme_id'] = ['in',$theme_ids];
            }
            $keyword = [$keywords => ['name', 'address', 'price','firm']];
            if (!$list = ActivityDetailRepository::search($keyword,$where,$activity_column,$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = ActivityDetailRepository::getList($where,$activity_column,'start_time','desc',$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $theme_ids  = array_column($list['data'],'theme_id');
        $themes     = ActivityThemeRepository::getList(['id' => ['in',$theme_ids]],['id','name']);
        foreach ($list['data'] as &$value){
            $value['price'] = empty($value['price']) ? '免费' : round($value['price'] / 100,2).'元';
            $theme = $this->searchArray($themes,'id',$value['theme_id']);
            $value['theme_name'] = $theme ? reset($theme)['name'] : '活动';
            $value['start_time']    = date('Y-m-d H:m:i',$value['start_time']);
            $value['end_time']      = date('Y-m-d H:m:i',$value['end_time']);
            $value['created_at']      = date('Y-m-d H:m:i',$value['created_at']);
            $value['updated_at']      = date('Y-m-d H:m:i',$value['updated_at']);
            $value['deleted_at']      = $value['deleted_at'] != 0 ? date('Y-m-d H:m:i',$value['deleted_at']) : '0';
            unset($value['theme_id']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 获取活动详情（后台详情）
     * @param $id
     * @return bool|null
     */
    public function activityDetail($id)
    {
        $column = ['id','name','address','price','theme_id','start_time','end_time','is_recommend','cover_id','banner_ids','image_ids','status','firm','notice','detail','is_member'];
        if (!$activity = ActivityDetailRepository::getOne(['id' => $id],$column)){
            $this->setError('活动不存在！');
            return false;
        }
        $activity['theme'] = ActivityThemeRepository::getField(['id' => $activity['theme_id']],'name');
        $activity['price'] = empty($activity['price']) ? '0' : round($activity['price'] / 100,2);
        $activity['start_time']    = date('Y-m-d H:m:i',$activity['start_time']);
        $activity['end_time']      = date('Y-m-d H:m:i',$activity['end_time']);
        $activity['images']     = [];
        if (!empty($activity['image_ids'])){
            $image_ids = explode(',',$activity['image_ids']);
            if ($image_list = CommonImagesRepository::getList(['id' => ['in', $image_ids]],['id','img_url'])){
//                $image_list     = array_column($image_list,'img_url');
                $activity['images']= $image_list;
            }
        }
        $activity['banners'] = [];
        if (!empty($activity['banner_ids'])){
            $image_ids = explode(',',$activity['banner_ids']);
            if ($image_list = CommonImagesRepository::getList(['id' => ['in', $image_ids]],['id','img_url'])){
                $activity['banners']= $image_list;
            }
        }
        $activity['cover'] = [];
        if (!empty($activity['cover_id'])){
            if ($cover_image = CommonImagesRepository::getOne(['id' => $activity['cover_id']],['id','img_url'])){
                $activity['cover']= $cover_image;
            }
        }
        #获取主办方
        $activity['host'] = [];
        if ($host_list = ActivityHostsRepository::getList(['activity_id' => $id],['id','type','name','logo_id'])){
            foreach ($host_list as &$value){
                $value['logo'] = CommonImagesRepository::getOne(['id' => $value['logo_id']],['id','img_url']);
            }
            $activity['host'] = $host_list;
        }
        #获取相关链接
        $activity['links'] = [];
        if ($link_list = ActivityLinksRepository::getList(['activity_id' => $id],['id','title','url','image_id'])){
            foreach ($link_list as &$value){
                $value['image'] = CommonImagesRepository::getField(['id' => $value['image_id']],'img_url');
            }
            $activity['links'] = $link_list;
        }
        $this->setMessage('获取成功！');
        return $activity;
    }


    /**
     * 小程序获取活动详情
     * @param $id
     * @return mixed
     */
    public function getActivityDetail($id){
        $column = ['id','name','address','price','theme_id','start_time','end_time','is_recommend','cover_id','banner_ids','image_ids','firm','notice','notice','detail'];
        if (!$activity = ActivityDetailRepository::getOne(['id' => $id],$column)){
            $this->setError('活动不存在！');
            return false;
        }
        $auth = Auth::guard('member_api');
        $member = $auth->user();
        $name = explode('·',$activity['name']);
        $activity['name']   = reset($name);
        $activity['title']  = count($name) > 1 ? end($name) : '';
        $activity['theme'] = ActivityThemeRepository::getField(['id' => $activity['theme_id']],'name');
        $activity['price'] = empty($activity['price']) ? '免费' : round($activity['price'] / 100,2).'元';
        $activity['images']     = [];
        if (!empty($activity['image_ids'])){
            $image_ids = explode(',',$activity['image_ids']);
            if ($image_list = CommonImagesRepository::getList(['id' => ['in', $image_ids]],['img_url'])){
                $image_list     = array_column($image_list,'img_url');
                $activity['images']= $image_list;
            }
        }
        $activity['banners'] = [];
        if (!empty($activity['banner_ids'])){
            $image_ids = explode(',',$activity['banner_ids']);
            if ($image_list = CommonImagesRepository::getList(['id' => ['in', $image_ids]],['img_url'])){
                $image_list     = array_column($image_list,'img_url');
                $activity['banners']= $image_list;
            }
        }
        if ($activity['start_time'] > time()){
            $activity['status'] = '报名中';
        }
        if ($activity['start_time'] < time() && $activity['end_time'] > time()){
            $activity['status'] = '进行中';
        }
        if ($activity['end_time'] < time()){
            $activity['status'] = '已结束';
        }
        #是否收藏
        $activity['is_collect'] = 0;
        if (ActivityCollectRepository::exists(['activity_id' => $activity['id'],'member_id' => $member->m_id])){
            $activity['is_collect'] = 1;
        }
        $start_time    = date('Y年m月d日',$activity['start_time']);
        $end_time      = date('m月d日',$activity['end_time']);
        $activity['activity_time']  = $start_time . '～' . $end_time;
        $activity['day_time']       = date('H:i',$activity['start_time']) .'-'.date('H:i',$activity['end_time']);
        $activity['cover'] = empty($activity['cover_id']) ? '':CommonImagesRepository::getField(['id' => $activity['cover_id']],'img_url');
        unset($activity['theme_id'],$activity['start_time'],$activity['end_time'],$activity['cover_id'],$activity['banner_ids'],$activity['image_ids']);
        #获取举办方信息
        $activity['hosts'] = [];
        if ($host_list = ActivityHostsRepository::getList(['activity_id' => $id,'type' => 1],['name','type','logo_id'])){
            foreach ($host_list as &$value){
                $value['logo'] = CommonImagesRepository::getField(['id' => $value['logo_id']],'img_url');
            }
            $activity['hosts'] = $host_list;
        }
        #获取相关链接
        $activity['links'] = [];
        if ($link_list = ActivityLinksRepository::getList(['activity_id' => $id],['id','title','url','image_id'])){
            foreach ($link_list as &$value){
                $value['image'] = CommonImagesRepository::getField(['id' => $value['image_id']],'img_url');
            }
            $activity['links'] = $link_list;
        }
        $this->setMessage('获取成功！');
        return $activity;
    }
}
            