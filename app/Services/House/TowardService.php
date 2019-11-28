<?php
namespace App\Services\House;


use App\Repositories\HouseDetailsRepository;
use App\Repositories\HouseTowardRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class TowardService extends BaseService
{
    use HelpTrait;

    /**
     * 添加朝向
     * @param $request
     * @return bool
     */
    public function addToward($request)
    {
        $add_arr = [
            'title'     => $request['title'],
        ];
        if (HouseTowardRepository::exists($add_arr)){
            $this->setError('朝向已存在！');
            return false;
        }
        $add_arr['describe']   = $request['describe'] ?? '';
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();
        if (HouseTowardRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }
    /**
     * 删除朝向
     * @param $id
     * @return bool
     */
    public function deleteToward($id)
    {
        if (!HouseTowardRepository::exists(['id' => $id])){
            $this->setError('朝向已删除！');
            return false;
        }
        if (HouseDetailsRepository::exists(['toward_id' => $id])){
            $this->setError('该朝向正在使用，无法删除！');
            return false;
        }
        if (HouseTowardRepository::delete(['id' => $id])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }


    /**
     * 修改朝向
     * @param $request
     * @return bool
     */
    public function editToward($request)
    {
        if (!HouseTowardRepository::exists(['id' => $request['id']])){
            $this->setError('朝向信息不存在！');
            return false;
        }
        $upd_arr = [
            'title'     => $request['title'],
        ];
        if (HouseTowardRepository::exists(array_merge($upd_arr,['id' => ['<>',$request['id']]]))){
            $this->setError('朝向已存在！');
            return false;
        }
        $upd_arr['describe']   = $request['describe'] ?? '';
        $upd_arr['updated_at'] = time();
        if (HouseTowardRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取朝向列表
     * @param $request
     * @return bool
     */
    public function getTowardList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        if (!$list = HouseTowardRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        foreach ($list['data'] as &$value){
            $value['created_at'] = date('Y-m-d H:i:s',$value['created_at']);
            $value['updated_at'] = date('Y-m-d H:i:s',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            