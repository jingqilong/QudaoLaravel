<?php
namespace App\Services\Activity;


use App\Repositories\ActivityDetailRepository;
use App\Repositories\ActivitySiteRepository;
use App\Repositories\ActivityThemeRepository;
use App\Repositories\CommonImagesRepository;
use App\Services\BaseService;

class SiteService extends BaseService
{

    /**
     * 添加活动场地
     * @param $request
     * @return bool
     */
    public function addSite($request)
    {
        if (ActivitySiteRepository::exists(['name' => $request['name']])){
            $this->setError('场地已存在！');
            return false;
        }
        if (!ActivityThemeRepository::exists(['id' => $request['theme_id']])){
            $this->setError('该主题不存在！');
            return false;
        }
        $add_arr = [
            'title'         => $request['title'],
            'address'       => $request['address'],
            'name'          => $request['name'],
            'theme_id'      => $request['theme_id'],
            'image_ids'     => $request['image_ids'],
            'labels'        => $request['labels'] ?? '',
            'scale'         => $request['scale'],
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (ActivitySiteRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }

    /**
     * 删除场地
     * @param $id
     * @return bool
     */
    public function deleteSite($id){
        if (!ActivitySiteRepository::exists(['id' => $id])){
            $this->setError('场地不存在！');
            return false;
        }
        if (ActivityDetailRepository::exists(['site_id' => $id])){
            $this->setError('该场地正在使用中，无法删除，只能修改！');
            return false;
        }
        if (ActivitySiteRepository::delete(['id' => $id])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }

    /**
     * 修改场地
     * @param $request
     * @return bool
     */
    public function editSite($request){
        if (!ActivitySiteRepository::exists(['id' => $request['id']])){
            $this->setError('场地不存在！');
            return false;
        }
        if (ActivitySiteRepository::exists(['name' => $request['name']])){
            $this->setError('场地名称已被使用！');
            return false;
        }
        $upd_arr = [
            'title'         => $request['title'],
            'address'       => $request['address'],
            'name'          => $request['name'],
            'theme_id'      => $request['theme_id'],
            'image_ids'     => $request['image_ids'],
            'labels'        => $request['labels'] ?? '',
            'scale'         => $request['scale'],
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (ActivitySiteRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }


    /**
     * 获取场景列表
     * @param $page
     * @param $page_num
     * @return bool|null
     */
    public function getSiteList($page, $page_num){
        if (!$list = ActivitySiteRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        unset($list['first_page_url'], $list['from'],
            $list['from'], $list['last_page_url'],
            $list['next_page_url'], $list['path'],
            $list['prev_page_url'], $list['to']);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        foreach ($list['data'] as &$value){
            $value['theme']      = ActivityThemeRepository::getField(['id' => $value['theme_id']],'name');
            $value['images']     = [];
            if (!empty($value['images_ids'])){
                $image_ids = explode(',',$value['images_ids']);
                if ($image_list = CommonImagesRepository::getList(['id' => ['in', $image_ids]],['img_url'])){
                    $image_list     = array_column($image_list,'img_url');
                    $value['images']= $image_list;
                }
            }
            $value['labels']        = !empty($value['labels']) ? explode('|',$value['labels']) : [];
            $value['created_at']    = date('Y-m-d H:m:i',$value['created_at']);
            $value['updated_at']    = date('Y-m-d H:m:i',$value['updated_at']);
            unset($value['theme_id'],$value['images_ids']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            