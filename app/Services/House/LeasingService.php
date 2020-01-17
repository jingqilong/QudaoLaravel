<?php
namespace App\Services\House;


use App\Repositories\HouseDetailsRepository;
use App\Repositories\HouseLeasingRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class LeasingService extends BaseService
{
    use HelpTrait;

    /**
     * 添加租赁方式
     * @param $request
     * @return bool
     */
    public function addLease($request)
    {
        $add_arr = [
            'title'     => $request['title'],
        ];
        if (HouseLeasingRepository::exists($add_arr)){
            $this->setError('租赁方式已存在！');
            return false;
        }
        $add_arr['describe']   = $request['describe'] ?? '';
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();
        if (HouseLeasingRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }
    /**
     * 删除租赁方式
     * @param $id
     * @return bool
     */
    public function deleteLease($id)
    {
        if (!HouseLeasingRepository::exists(['id' => $id])){
            $this->setError('租赁方式已删除！');
            return false;
        }
        if (HouseDetailsRepository::exists(['leasing_id' => $id])){
            $this->setError('该租赁方式已使用，无法删除！');
            return false;
        }
        if (HouseLeasingRepository::delete(['id' => $id])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }


    /**
     * 修改租赁方式
     * @param $request
     * @return bool
     */
    public function editLease($request)
    {
        if (!HouseLeasingRepository::exists(['id' => $request['id']])){
            $this->setError('租赁方式信息不存在！');
            return false;
        }
        $upd_arr = [
            'title'     => $request['title'],
        ];
        if (HouseLeasingRepository::exists(array_merge($upd_arr,['id' => ['<>',$request['id']]]))){
            $this->setError('租赁方式已存在！');
            return false;
        }
        $upd_arr['describe']   = $request['describe'] ?? '';
        $upd_arr['updated_at'] = time();
        if (HouseLeasingRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取租赁方式列表
     * @param $request
     * @return bool
     */
    public function getLeaseList($request)
    {
        if (!$list = HouseLeasingRepository::getList(['id' => ['>',0]],['*'],'id','asc')){
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
            