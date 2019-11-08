<?php


namespace App\Repositories;


use App\Models\ActivityDetailModel;
use App\Repositories\Traits\RepositoryTrait;
use App\Traits\HelpTrait;

class ActivityDetailRepository extends ApiRepository
{
    use RepositoryTrait,HelpTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(ActivityDetailModel $model)
    {
        $this->model = $model;
    }

    /**
     * 前端获取活动列表数据格式
     * @param $where
     * @param $column
     * @param $order
     * @param $desc_asc
     * @param $page
     * @param $pageNum
     * @return mixed
     */
    protected function getActivityList($where,array $column=['*'], $order=null, $desc_asc=null, $page=null, $pageNum=null){
        if (!$list = ActivityDetailRepository::getList($where,$column,$order,$desc_asc,$page,$pageNum)){
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
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
            #处理地址
            list($area_address) = $this->makeAddress($value['area_code'],'',3);
            $value['address']  = $area_address;
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
            $start_time    = date('Y年m/d',$value['start_time']);
            $end_time      = date('m/d',$value['end_time']);
            $value['activity_time'] = $start_time . '-' . $end_time;
            $value['cover'] = empty($value['cover_id']) ? '':CommonImagesRepository::getField(['id' => $value['cover_id']],'img_url');
            unset($value['theme_id'],$value['start_time'],$value['end_time'],$value['cover_id']);
        }
        return $list;
    }

    /**
     * 前端搜索获取活动列表数据格式
     * @param array $keywords
     * @param $where
     * @param array $column
     * @param $order
     * @param $desc_asc
     * @param $page
     * @param $pageNum
     * @return mixed
     */
    protected function getSearchActivityList(array $keywords,$where,array $column=['*'], $order=null, $desc_asc=null, $page=null, $pageNum=null){
        if (!$list = ActivityDetailRepository::search($keywords,$where,$column,$order,$desc_asc,$page,$pageNum)){
            return false;
        }
        unset($list['first_page_url'], $list['from'],
            $list['from'], $list['last_page_url'],
            $list['next_page_url'], $list['path'],
            $list['prev_page_url'], $list['to']);
        if (empty($list['data'])){
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
        return $list;
    }
}
            