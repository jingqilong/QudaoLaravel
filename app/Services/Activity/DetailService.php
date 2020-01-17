<?php
namespace App\Services\Activity;


use App\Enums\ActivityEnum;
use App\Enums\ActivityRegisterAuditEnum;
use App\Enums\ActivityRegisterStatusEnum;
use App\Enums\ActivityStopSellingEnum;
use App\Enums\CollectTypeEnum;
use App\Repositories\{
    ActivityDetailRepository,
    ActivityGuestRepository,
    ActivityHostsRepository,
    ActivityLinksRepository,
    ActivityRegisterRepository,
    ActivityThemeRepository,
    CommonAreaRepository,
    CommonImagesRepository,
    MemberCollectRepository};
use App\Services\BaseService;
use App\Services\Common\ImagesService;
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
        if ($request['start_time'] >= $request['end_time']){
            $this->setError('活动开始时间必须小于结束时间！');
            return false;
        }
        $area_codes = explode(',',$request['area_code']);
        if (count($area_codes) != CommonAreaRepository::count(['code' => ['in',$area_codes]])){
            $this->setError('无效的地区代码！');
            return false;
        }
        if (!ActivityThemeRepository::exists(['id' => $request['theme_id']])){
            $this->setError('活动主题不存在！');
            return false;
        }
        $is_recommend = $request['is_recommend'] ?? 0;
        $add_arr = [
            'name'          => $request['name'],
            'area_code'     => $request['area_code'] . ',',
            'longitude'     => $request['longitude'] ?? '',
            'latitude'      => $request['latitude'] ?? '',
            'address'       => $request['address'],
            'price'         => isset($request['price']) ? $request['price'] * 100 : 0,
            'theme_id'      => $request['theme_id'],
            'signin'        => $request['signin'] ?? 0,
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
            'need_audit'    => $request['need_audit'],
            'stop_selling'  => $request['stop_selling'] ?? ActivityStopSellingEnum::NORMAL_SELLING,
            'max_number'    => $request['max_number'] ?? 0,
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
        $this->setPerPage($request['page_num']);
        $theme_id       = $request['theme_id'] ?? null;
        $is_recommend   = $request['is_recommend'] ?? null;
        $price          = $request['price'] ?? null;
        $status         = $request['status'] ?? null;
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
        if (!empty($price)){
            $where['price']  = $price == 1 ? 0 : ['>',0];
        }
        if (!empty($status)){
            switch ($status){
                case 1:
                    $where['start_time'] = ['>',time()];
                    break;
                case 2:
                    $where['start_time'] = ['<',time()];
                    $where['end_time'] = ['>',time()];
                    break;
                case 3:
                    $where['end_time'] = ['<',time()];
                    break;
            }
        }
        $activity_column = ['id','name','area_code','address','price','start_time','end_time','cover_id','theme_id','stop_selling'];
        if (!empty($keywords)){
            $keyword = [$keywords => ['name', 'address', 'price']];
            if (!$list = ActivityDetailRepository::search($keyword,$where,$activity_column,$order,$desc_asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = ActivityDetailRepository::getList($where,$activity_column,$order,$desc_asc)){
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
        $themes     = ActivityThemeRepository::getAllList(['id' => ['in',$theme_ids]],['id','name','icon_id']);
        $icon_ids   = array_column($themes,'icon_id');
        $icons      = CommonImagesRepository::getAllList(['id' => ['in',$icon_ids]]);
        foreach ($list['data'] as &$value){
            $theme = $this->searchArray($themes,'id',$value['theme_id']);
            if ($theme)
                $icon  = $this->searchArray($icons,'id',reset($theme)['icon_id']);
            #处理地址
            list($area_address) = $this->makeAddress($value['area_code'],'',3);
            $value['address']  = $area_address;
            $value['theme_name'] = $theme ? reset($theme)['name'] : '活动';
            $value['theme_icon'] = $icons ? reset($icon)['img_url'] : '';
            $value['price'] = empty($value['price']) ? '免费' : round($value['price'] / 100,2).'元';
            if ($value['start_time'] > time()){
                $value['status'] = 1;
                $value['status_title'] = $value['stop_selling'] == ActivityStopSellingEnum::STOP_SELLING ? '已售罄' : '报名中';
            }
            if ($value['start_time'] < time() && $value['end_time'] > time()){
                $value['status'] = 2;
                $value['status_title'] = '进行中';
            }
            if ($value['end_time'] < time()){
                $value['status'] = 3;
                $value['status_title'] = '已结束';
            }
            $start_time    = date('Y年m/d',$value['start_time']);
            $end_time      = date('m/d',$value['end_time']);
            $value['activity_time'] = $start_time . '-' . $end_time;
            $value['cover'] = empty($value['cover_id']) ? '':CommonImagesRepository::getField(['id' => $value['cover_id']],'img_url');
            unset($value['theme_id'],$value['start_time'],$value['end_time'],$value['cover_id'],$value['area_code'],$value['stop_selling']);
        }
        $this->setMessage('获取成功！');
        return $list;
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
        if ($request['start_time'] >= $request['end_time']){
            $this->setError('活动开始时间必须小于结束时间！');
            return false;
        }
        if (!$activity = ActivityDetailRepository::getOne(['id' => $request['id']])){
            $this->setError('活动不存在！');
            return false;
        }
        $area_codes = explode(',',$request['area_code']);
        if (count($area_codes) != CommonAreaRepository::count(['code' => ['in',$area_codes]])){
            $this->setError('无效的地区代码！');
            return false;
        }
        if (!ActivityThemeRepository::exists(['id' => $request['theme_id']])){
            $this->setError('活动主题不存在！');
            return false;
        }
        $is_recommend = $request['is_recommend'] ?? 0;
        $upd_arr = [
            'name'          => $request['name'],
            'area_code'     => $request['area_code'] . ',',
            'longitude'     => $request['longitude'] ?? '',
            'latitude'      => $request['latitude'] ?? '',
            'address'       => $request['address'],
            'price'         => isset($request['price']) ? $request['price'] * 100 : 0,
            'theme_id'      => $request['theme_id'],
            'signin'        => $request['signin'] ?? 0,
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
            'need_audit'    => $request['need_audit'],
            'stop_selling'  => $request['stop_selling'] ?? ActivityStopSellingEnum::NORMAL_SELLING,
            'max_number'    => $request['max_number'] ?? 0,
        ];
        if (ActivityDetailRepository::exists(array_merge($upd_arr,['id' => ['<>',$request['id']]]))){
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
        $start_time     = $request['start_time'] ?? null;
        $end_time       = $request['end_time'] ?? null;
        $is_recommend   = $request['is_recommend'] ?? null;
        $status         = $request['status'] ?? null;
        $is_member      = $request['is_member'] ?? null;
        $need_audit     = $request['need_audit'] ?? null;
        $keywords       = $request['keywords'] ?? null;
        $time_sort      = $request['time_sort'] ?? 1;
        $where          = ['id' => ['>',0],'deleted_at' => 0];
        $sort           = ['id','created_at'];
        $asc            = $time_sort == 1 ? ['desc','desc'] : ['asc','asc'];
        if (!is_null($start_time)){
            $where['start_time']    = ['>',strtotime($start_time)];
        }
        if (!is_null($end_time)){
            $where['end_time']      = ['<',strtotime($end_time)];
        }
        if ($is_recommend != null){
            $where['is_recommend']  = $is_recommend == 0 ? 0 : ['<>',0];
        }
        if (!is_null($status)){
            $where['status']  = $status;
        }
        if (!is_null($is_member)){
            $where['is_member']  = $is_member;
        }
        if (!is_null($need_audit)){
            $where['need_audit']  = $need_audit;
        }
        $activity_column = ['id','name','area_code','address','price','start_time','end_time','is_recommend','status','theme_id','signin','firm','is_member','need_audit','stop_selling','max_number','created_at','updated_at','deleted_at'];
        if (!empty($keywords)){
            if ($search_themes = ActivityThemeRepository::getList(['name' => $keywords],['id'])){
                $theme_ids = array_column($search_themes,'id');
                $where['theme_id'] = ['in',$theme_ids];
            }
            $keyword = [$keywords => ['name', 'address', 'price','firm']];
            if (!$list = ActivityDetailRepository::search($keyword,$where,$activity_column,$sort,$asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = ActivityDetailRepository::getList($where,$activity_column,$sort,$asc)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $theme_ids      = array_column($list['data'],'theme_id');
        $themes         = ActivityThemeRepository::getList(['id' => ['in',$theme_ids]],['id','name']);
        $activity_ids   = array_column($list['data'],'id');
        $host_list      = ActivityHostsRepository::getAllList(['activity_id' => ['in',$activity_ids]],['activity_id','type','name','logo_id']);
        $host_list      = ImagesService::getListImages($host_list,['logo_id' => 'single']);
        $link_list      = ActivityLinksRepository::getAllList(['activity_id' => ['in',$activity_ids]],['activity_id','title','url','image_id']);
        $link_list      = ImagesService::getListImages($link_list,['image_id' => 'single']);
        foreach ($list['data'] as &$value){
            $value['hosts'] = '';
            if ($hosts = $this->searchArray($host_list,'activity_id',$value['id'])){
                foreach ($hosts as &$v)unset($v['activity_id']);
                $value['hosts'] = json_encode($hosts);
            }
            $value['links'] = '';
            if ($links = $this->searchArray($link_list,'activity_id',$value['id'])){
                foreach ($links as &$v)unset($v['activity_id']);
                $value['links'] = json_encode($links);
            }

            $value['price'] = empty($value['price']) ? '免费' : round($value['price'] / 100,2).'元';
            $theme = $this->searchArray($themes,'id',$value['theme_id']);
            #处理地址
            list($area_address) = $this->makeAddress($value['area_code'],$value['address'],1,true);
            $value['area_address']  = $area_address;
            $value['is_recommend']  = $value['is_recommend'] != 0 ? 1 : 0;
            $value['theme_name']    = $theme ? reset($theme)['name'] : '活动';
            $value['start_time']    = date('Y-m-d H:m',$value['start_time']);
            $value['end_time']      = date('Y-m-d H:m',$value['end_time']);
            $value['created_at']    = date('Y-m-d H:m:i',$value['created_at']);
            $value['updated_at']    = date('Y-m-d H:m:i',$value['updated_at']);
            $value['deleted_at']    = $value['deleted_at'] != 0 ? date('Y-m-d H:m:i',$value['deleted_at']) : '0';
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
        $column = ['id','name','area_code','longitude','latitude','address','price','theme_id','signin','start_time','end_time','is_recommend','cover_id','banner_ids','image_ids','status','firm','notice','detail','is_member','need_audit','stop_selling','max_number'];
        if (!$activity = ActivityDetailRepository::getOne(['id' => $id],$column)){
            $this->setError('活动不存在！');
            return false;
        }
        list($area_address) = $this->makeAddress($activity['area_code'],$activity['address'],1,true);
        $activity['area_address']  = $area_address;
        $activity['theme']         = ActivityThemeRepository::getField(['id' => $activity['theme_id']],'name');
        $activity['price']         = empty($activity['price']) ? '0' : round($activity['price'] / 100,2);
        $activity['start_time']    = date('Y-m-d H:m',$activity['start_time']);
        $activity['end_time']      = date('Y-m-d H:m',$activity['end_time']);
        $activity['images']        = [];
        if (!empty($activity['image_ids'])){
            $image_ids = explode(',',$activity['image_ids']);
            if ($image_list = CommonImagesRepository::getAllList(['id' => ['in', $image_ids]],['id','img_url'])){
//                $image_list     = array_column($image_list,'img_url');
                $activity['images']= $image_list;
            }
        }
        $activity['banners'] = [];
        if (!empty($activity['banner_ids'])){
            $image_ids = explode(',',$activity['banner_ids']);
            if ($image_list = CommonImagesRepository::getAllList(['id' => ['in', $image_ids]],['id','img_url'])){
                $activity['banners']= $image_list;
            }
        }
        $activity['cover'] = [];
        if (!empty($activity['cover_id'])){
            if ($cover_image = CommonImagesRepository::getOne(['id' => $activity['cover_id']],['id','img_url'])){
                $activity['cover'] = $cover_image;
            }
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
        $column = ['id','name','area_code','address','price','longitude','latitude','theme_id','start_time','end_time','is_recommend','cover_id','banner_ids','image_ids','firm','notice','notice','detail','stop_selling'];
        if (!$activity = ActivityDetailRepository::getOne(['id' => $id],$column)){
            $this->setError('活动不存在！');
            return false;
        }
        $auth = Auth::guard('member_api');
        $member = $auth->user();
        $name = explode('·',$activity['name']);
        $activity['name']       = reset($name);
        $activity['title']      = count($name) > 1 ? end($name) : '';
        list($area_address)     = $this->makeAddress($activity['area_code'],$activity['address'],1,true);
        $activity['address']    = $area_address;
        $activity['theme']      = ActivityThemeRepository::getField(['id' => $activity['theme_id']],'name');
        $activity['price']      = empty($activity['price']) ? '免费' : round($activity['price'] / 100,2).'元';
        $activity['images']     = [];
        if (!empty($activity['image_ids'])){
            $image_ids = explode(',',$activity['image_ids']);
            if ($image_list = CommonImagesRepository::getAllList(['id' => ['in', $image_ids]],['img_url'])){
                $image_list         = array_column($image_list,'img_url');
                $activity['images'] = $image_list;
            }
        }
        $activity['banners'] = [];
        if (!empty($activity['banner_ids'])){
            $image_ids = explode(',',$activity['banner_ids']);
            if ($image_list = CommonImagesRepository::getAllList(['id' => ['in', $image_ids]],['img_url'])){
                $image_list         = array_column($image_list,'img_url');
                $activity['banners']= $image_list;
            }
        }
        if ($activity['start_time'] > time()){
            $activity['status'] = 1;
            $activity['status_title'] = $activity['stop_selling'] == ActivityStopSellingEnum::STOP_SELLING ? '已售罄' : '报名中';
        }
        if ($activity['start_time'] < time() && $activity['end_time'] > time()){
            $activity['status'] = 2;
            $activity['status_title'] = '进行中';
        }
        if ($activity['end_time'] < time()){
            $activity['status'] = 3;
            $activity['status_title'] = '已结束';
        }
        $count_where = ['activity_id' => $id,'status' => ['in',[ActivityRegisterStatusEnum::COMPLETED,ActivityRegisterStatusEnum::EVALUATION]],'audit' => ActivityRegisterAuditEnum::PASS];
        if (!$register_count = ActivityRegisterRepository::count($count_where)){
            $register_count = 0;
        }
        #如果票已卖完，则改活动售票状态为已售罄
        if (!empty($activity['max_number']) && ($activity['max_number'] <= $register_count)){
            $activity['stop_selling'] = ActivityStopSellingEnum::STOP_SELLING;
        }
        #是否收藏
        $activity['is_collect'] = 0;
        if (MemberCollectRepository::exists(['type' => CollectTypeEnum::ACTIVITY,'target_id' => $activity['id'],'member_id' => $member->id,'deleted_at' => 0])){
            $activity['is_collect'] = 1;
        }
        $start_time    = date('Y年m月d日',$activity['start_time']);
        $end_time      = date('m月d日',$activity['end_time']);
        $activity['activity_time']  = $start_time . '～' . $end_time;
        $activity['day_time']       = date('H:i',$activity['start_time']) .'-'.date('H:i',$activity['end_time']);
        $activity['cover']          = empty($activity['cover_id']) ? '':CommonImagesRepository::getField(['id' => $activity['cover_id']],'img_url');
        unset($activity['theme_id'],$activity['start_time'],$activity['end_time'],$activity['cover_id'],$activity['banner_ids'],$activity['image_ids'],$activity['area_code']);
        #获取举办方信息
        $activity['hosts'] = $this->getActivityHosts($id);

        #获取相关链接
        $activity['links'] = $this->getActivityLinks($id);

        //判断用户是否已报名
        $activity['register_id'] = 0;
        $activity['register_status'] = 0;
        $activity['order_no'] = '';
        $activity['sign_in_code'] = '';
        $activity_where = [
            'member_id'     => $member->id,
            'activity_id'   => $id,
            'audit'         => ['<>',ActivityRegisterAuditEnum::TURN_DOWN],
            'status'        => ['<>',ActivityRegisterStatusEnum::CANCELED]];
        if ($register = ActivityRegisterRepository::getOrderOne($activity_where,'created_at')){
            $activity['register_id']     = $register['id'];
            $activity['register_status'] = $register['status'];
            $activity['register_audit']  = $register['audit'];
            $activity['sign_in_code']    = $register['sign_in_code'];
            if ($register['status'] == ActivityRegisterStatusEnum::EVALUATION || $register['status'] == ActivityRegisterStatusEnum::COMPLETED ){
                $activity['order_no'] = $register['order_no'];
            }
        }
        $activity['images']     = $this->suffix($activity['images'],1);
        $activity['banners']    = $this->suffix($activity['banners'],1);
        $activity['cover']      = $this->suffix($activity['cover'],1);
        $activity['past_activities'] = [];
        if ($past_activities = ActivityDetailRepository::getList(['status' => ActivityEnum::OPEN,'end_time' => ['<',time()]],['id','cover_id'],'start_time','desc',1,4)){
            if ($past_activities['data']){
                $activity['past_activities'] = ImagesService::getListImagesConcise($past_activities['data'],['cover_id' => 'single']);
            }
        }
        $this->setMessage('获取成功！');
        return $activity;
    }

    /**
     * 获取状态开关
     * @param $request
     * @return bool
     */
    public function activitySwitch($request)
    {
        $status         = $request['status'] ?? null;
        $is_recommend   = $request['is_recommend'] ?? null;
        $is_member      = $request['is_member'] ?? null;
        $need_audit     = $request['need_audit'] ?? null;
        $stop_selling   = $request['stop_selling'] ?? null;
        if (!ActivityDetailRepository::exists(['id' => $request['id'],'deleted_at' => 0])){
            $this->setError('活动不存在！');
            return false;
        }
        $upd_arr = ['updated_at' => time()];
        if (!is_null($status)){
            $upd_arr['status'] = $status;
        }
        if (!is_null($is_recommend)){
            $upd_arr['is_recommend'] = $is_recommend == 0 ? 0 : time();
        }
        if (!is_null($is_member)){
            $upd_arr['is_member'] = $is_member;
        }
        if (!is_null($need_audit)){
            $upd_arr['need_audit'] = $need_audit;
        }
        if (!is_null($stop_selling)){
            $upd_arr['stop_selling'] = $stop_selling;
        }
        if (!ActivityDetailRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('操作失败！');
            return false;
        }
        $this->setMessage('操作成功！');
        return true;
    }

    /**
     * 获取活动相关链接
     * @param $activity_id
     * @return array|null
     */
    public function getActivityLinks($activity_id){
        $links = [];
        if ($link_list = ActivityLinksRepository::getAllList(['activity_id' => $activity_id],['id','title','url','image_id'])){
            foreach ($link_list as &$value){
                $value['image'] = CommonImagesRepository::getField(['id' => $value['image_id']],'img_url');
            }
            $links = $link_list;
        }
        return $links;
    }

    /**
     * 获取活动举办方信息
     * @param $activity_id
     * @return array|null
     */
    public function getActivityHosts($activity_id){
        $hosts = [];
        if ($host_list = ActivityHostsRepository::getAllList(['activity_id' => $activity_id,'type' => 1],['name','type','logo_id'])){
            foreach ($host_list as &$value){
                $value['logo'] = CommonImagesRepository::getField(['id' => $value['logo_id']],'img_url');
            }
            $hosts = $host_list;
        }
        return $hosts;
    }
}
            