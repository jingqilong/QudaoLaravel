<?php
namespace App\Services\Event;


use App\Repositories\ActivityDetailRepository;
use App\Repositories\ActivitySiteRepository;
use App\Repositories\ActivityThemeRepository;
use App\Services\BaseService;

class ActivityService extends BaseService
{

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
            'detail'        => $request['detail'] ?? ''
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
}
            