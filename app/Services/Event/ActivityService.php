<?php
namespace App\Services\Event;


use App\Repositories\ActivityDetailRepository;
use App\Repositories\ActivityGuestRepository;
use App\Repositories\ActivityRegisterRepository;
use App\Repositories\ActivitySiteRepository;
use App\Repositories\ActivityThemeRepository;
use App\Repositories\CommonImagesRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class ActivityService extends BaseService
{
    use HelpTrait;

    /**
     * 添加活动
     * @param $request
     * @return bool
     */
    public function addActivity($request)
    {
        if (!ActivityThemeRepository::exists(['id' => $request['theme_id']])){
            $this->setError('活动主题不存在！');
            return false;
        }
        if (!ActivitySiteRepository::exists(['id' => $request['site_id']])){
            $this->setError('活动场地不存在！');
            return false;
        }
        $add_arr = [
            'name'          => $request['name'],
            'address'       => $request['address'],
            'price'         => isset($request['price']) ? $request['price'] * 100 : 0,
            'theme_id'      => $request['theme_id'],
            'start_time'    => strtotime($request['start_time']),
            'end_time'      => strtotime($request['end_time']),
            'site_id'       => $request['site_id'],
            'supplies_ids'  => $request['supplies_ids'] ?? '',
            'is_recommend'  => $request['is_recommend'] ?? 0,
            'banner_ids'    => $request['banner_ids'],
            'image_ids'     => $request['image_ids'],
            'status'        => $request['status'],
            'firm'          => $request['firm'] ?? '',
            'notice'        => $request['notice'] ?? '',
            'detail'        => $request['detail'] ?? '',
            'is_member'     => $request['is_member']
        ];
        if (ActivityDetailRepository::exists($add_arr)){
            $this->setError('该活动已添加！');
            return false;
        }
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();

        if (ActivityDetailRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 活动首页列表【有搜索功能】
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
        $where          = ['status' => 1,'end_time' => ['>',time()]];
        if (!empty($theme_id)){
            $where['theme_id']  = $theme_id;
        }
        if (!empty($is_recommend)){
            $where['is_recommend']  = $is_recommend;
        }
        $activity_column = ['id','name','address','price','start_time','end_time','site_id','is_recommend','banner_ids','firm','image_ids','theme_id'];
        if (!empty($keywords)){
            $keyword = [$keywords => ['name', 'address', 'price']];
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
        unset($list['first_page_url'], $list['from'],
            $list['from'], $list['last_page_url'],
            $list['next_page_url'], $list['path'],
            $list['prev_page_url'], $list['to']);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $theme_ids  = array_column($list['data'],'theme_id');
        $themes     = ActivityThemeRepository::getList(['id' => ['in',$theme_ids]],['name']);
        foreach ($list['data'] as &$value){
            $value['price'] = empty($value['price']) ? '免费' : round($value['price'] / 100,2).'元';
            $value['images']     = [];
            $theme = $this->searchArray($themes,'id',$value['theme_id']);
            $value['theme_name'] = $theme ? reset($theme)['name'] : '活动';
            if (!empty($value['image_ids'])){
                $image_ids = explode(',',$value['image_ids']);
                if ($image_list = CommonImagesRepository::getList(['id' => ['in', $image_ids]],['img_url'])){
                    $image_list     = array_column($image_list,'img_url');
                    $value['images']= $image_list;
                }
            }
            $value['banners'] = [];
            if (!empty($value['banner_ids'])){
                $image_ids = explode(',',$value['banner_ids']);
                if ($image_list = CommonImagesRepository::getList(['id' => ['in', $image_ids]],['img_url'])){
                    $image_list     = array_column($image_list,'img_url');
                    $value['banners']= $image_list;
                }
            }
            $value['firm'] = !empty($value['firm']) ? explode('|',$value['firm']): [];
            $site                   = ActivitySiteRepository::getOne(['id' => $value['site_id']]);
            $value['site_name']     = $site ? $site['name'] : '';
            $value['site_title']    = $site ? $site['title'] : '';
            $value['start_time']    = date('Y-m-d H:m:i',$value['start_time']);
            $value['end_time']      = date('Y-m-d H:m:i',$value['end_time']);
            unset($value['image_ids'],$value['banner_ids'],$value['theme_id'],$value['site_id']);
        }
        $this->setMessage('获取成功！');
        $res['banners']     = [];
        $res['list']        = $list;
        return $res;
    }

    /**
     * 软删除活动
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
        if (!ActivitySiteRepository::exists(['id' => $request['site_id']])){
            $this->setError('活动场地不存在！');
            return false;
        }
        $upd_arr = [
            'name'          => $request['name'],
            'address'       => $request['address'],
            'price'         => isset($request['price']) ? $request['price'] * 100 : 0,
            'theme_id'      => $request['theme_id'],
            'start_time'    => strtotime($request['start_time']),
            'end_time'      => strtotime($request['end_time']),
            'site_id'       => $request['site_id'],
            'supplies_ids'  => $request['supplies_ids'] ?? '',
            'is_recommend'  => $request['is_recommend'] ?? 0,
            'banner_ids'    => $request['banner_ids'],
            'image_ids'     => $request['image_ids'],
            'status'        => $request['status'],
            'firm'          => $request['firm'] ?? '',
            'notice'        => $request['notice'] ?? '',
            'detail'        => $request['detail'] ?? '',
            'is_member'     => $request['is_member']
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
}
            