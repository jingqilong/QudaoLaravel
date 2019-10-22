<?php
namespace App\Services\Activity;


use App\Repositories\ActivityDetailRepository;
use App\Repositories\ActivityLinksRepository;
use App\Services\BaseService;

class LinksService extends BaseService
{

    /**
     * 给活动添加相关链接
     * @param $request
     * @return bool
     */
    public function addLink($request)
    {
        if (!ActivityDetailRepository::exists(['id' => $request['activity_id']])){
            $this->setError('活动不存在！');
            return false;
        }
        $parameter = json_decode($request['parameters'],true);
        $add_arr = [];
        foreach ($parameter as $value){
            if (!isset($value['title'])){
                $this->setError('链接标签不能为空！');
                return false;
            }
            if (!isset($value['url'])){
                $this->setError('链接不能为空！');
                return false;
            }
            if (!empty($value['image_id']) && !is_integer($value['image_id'])){
                $this->setError('链接图ID必须为整数！');
                return false;
            }
            $add_arr[] = [
                'activity_id'   => $request['activity_id'],
                'title'         => $value['title'],
                'url'           => $value['url'],
                'image_id'      => $value['image_id'],
                'created_at'    => time(),
                'updated_at'    => time(),
            ];
        }
        if (!ActivityLinksRepository::create($add_arr)){
            $this->setError('添加失败！');
            return false;
        }
        $this->setMessage('添加成功！');
        return true;
    }

    /**
     * 删除链接
     * @param $id
     * @return bool
     */
    public function deleteLink($id)
    {
        if (!ActivityLinksRepository::exists(['id' => $id])){
            $this->setError('链接不存在！');
            return false;
        }
        if (!ActivityLinksRepository::delete(['id' => $id])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 编辑链接
     * @param $request
     * @return bool
     */
    public function editLink($request)
    {
        if (!ActivityLinksRepository::exists(['id' => $request['id']])){
            $this->setError('链接不存在！');
            return false;
        }
        $upd_arr = [
            'title'         => $request['title'],
            'url'           => $request['url'],
            'image_id'      => $request['image_id'],
            'updated_at'    => time()
        ];
        if (!ActivityLinksRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('修改失败！');
            return false;
        }
        $this->setMessage('修改成功！');
        return true;
    }
}
            