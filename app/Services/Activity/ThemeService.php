<?php
namespace App\Services\Activity;


use App\Repositories\ActivityDetailRepository;
use App\Repositories\ActivitySiteRepository;
use App\Repositories\ActivitySuppliesRepository;
use App\Repositories\ActivityThemeRepository;
use App\Services\BaseService;

class ThemeService extends BaseService
{

    /**
     * 添加主题
     * @param $request
     * @return bool
     */
    public function addTheme($request)
    {
        if (ActivityThemeRepository::exists(['name' => $request['name']])){
            $this->setError('主题已存在！');
            return false;
        }
        $add_arr = [
            'name'          => $request['name'],
            'description'   => $request['description'] ?? '',
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (ActivityThemeRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }
    /**
     * 删除主题
     * @param $id
     * @return bool
     */
    public function deleteTheme($id)
    {
        if (!ActivityThemeRepository::exists(['id' => $id])){
            $this->setError('主题不存在！');
            return false;
        }
        if (ActivityDetailRepository::exists(['theme_id' => $id])
            || ActivitySiteRepository::exists(['theme_id' => $id])
            || ActivitySuppliesRepository::exists(['theme_id' => $id])){
            $this->setError('该主题正在使用中，无法删除，只能修改！');
            return false;
        }
        if (ActivityThemeRepository::delete(['id' => $id])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }


    /**
     * 修改主题
     * @param $request
     * @return bool
     */
    public function editTheme($request)
    {
        if (!ActivityThemeRepository::exists(['id' => $request['id']])){
            $this->setError('主题不存在！');
            return false;
        }
        if (ActivityThemeRepository::exists(['name' => $request['name']])){
            $this->setError('主题名称已被使用！');
            return false;
        }
        $upd_arr = [
            'name'          => $request['name'],
            'description'   => $request['description'] ?? '',
            'updated_at'    => time(),
        ];
        if (ActivityThemeRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取主题列表
     * @param $page
     * @param $page_num
     * @return bool
     */
    public function getThemeList($page, $page_num)
    {
        if (!$list = ActivityThemeRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$page_num)){
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
            $value['created_at'] = date('Y-m-d H:m:i',$value['created_at']);
            $value['updated_at'] = date('Y-m-d H:m:i',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            