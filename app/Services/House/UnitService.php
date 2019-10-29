<?php
namespace App\Services\House;


use App\Repositories\HouseDetailsRepository;
use App\Repositories\HouseUnitRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class UnitService extends BaseService
{
    use HelpTrait;

    /**
     * 添加朝向
     * @param $request
     * @return bool
     */
    public function addUnit($request)
    {
        $add_arr = [
            'title'     => $request['title'],
        ];
        if (HouseUnitRepository::exists($add_arr)){
            $this->setError('户型已存在！');
            return false;
        }
        $add_arr['describe']   = $request['describe'] ?? '';
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();
        if (HouseUnitRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }
    /**
     * 删除户型
     * @param $id
     * @return bool
     */
    public function deleteUnit($id)
    {
        if (!HouseUnitRepository::exists(['id' => $id])){
            $this->setError('户型已删除！');
            return false;
        }
        if (HouseDetailsRepository::exists(['unit_id' => $id])){
            $this->setError('该户型正在使用，无法删除！');
            return false;
        }
        if (HouseUnitRepository::delete(['id' => $id])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }


    /**
     * 修改户型
     * @param $request
     * @return bool
     */
    public function editUnit($request)
    {
        if (!HouseUnitRepository::exists(['id' => $request['id']])){
            $this->setError('户型信息不存在！');
            return false;
        }
        $upd_arr = [
            'title'     => $request['title'],
        ];
        if (HouseUnitRepository::exists(array_merge($upd_arr,['id' => ['<>',$request['id']]]))){
            $this->setError('户型已存在！');
            return false;
        }
        $upd_arr['describe']   = $request['describe'] ?? '';
        $upd_arr['updated_at'] = time();
        if (HouseUnitRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取户型列表
     * @param $request
     * @return bool
     */
    public function getUnitList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        if (!$list = HouseUnitRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$page_num)){
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
            