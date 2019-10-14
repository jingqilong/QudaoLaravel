<?php
namespace App\Services\Project;


use App\Repositories\ProjectOrderRepository;
use App\Services\BaseService;

class ProjectService extends BaseService
{

    /**
     * 获取项目对接订单列表  （前端使用）
     * @param array $data
     * @return mixed
     */
    public function getProjectList(array $data)
    {
        if (!$list = ProjectOrderRepository::getList(['name' => $data['name'],'status' => ['in',[1,2,3,4]]])){
            $this->setMessage('没有数据！');
            return [];
        }
        foreach ($list as &$value)
        {
            $value['reservation_at']    =   date('Y-m-d H:m:s',$value['reservation_at']);
            $value['created_at']        =   date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at']        =   date('Y-m-d H:m:s',$value['updated_at']);
        }
        $this->setMessage('查找成功');
        return $list;
    }

    /**
     * 获取项目对接订单详情
     * @param string $id
     * @return mixed
     */
    public function getProjectInfo(string $id)
    {
        if (!$list = ProjectOrderRepository::getOne(['id' => $id,'status' => ['in',[1,2,3,4]]])){
            $this->setError('查询不到该条数据！');
            return false;
        }

        $list['reservation_at']    =   date('Y-m-d H:m:s',$list['reservation_at']);
        $list['created_at']        =   date('Y-m-d H:m:s',$list['created_at']);
        $list['updated_at']        =   date('Y-m-d H:m:s',$list['updated_at']);

        $this->setMessage('查找成功');
        return $list;
    }


    /**
     * 添加项目订单信息
     * @param array $data
     * @return mixed
     */
    public function addProject(array $data)
    {
        unset($data['sign'], $data['token']);
        $data['created_at']     = time();
        $data['reservation_at'] = strtotime($data['reservation_at']);
        $data['status']         = '1';

        if (!$res = ProjectOrderRepository::getAddId($data)){
            $this->setError('预约失败,请重试！');
            return false;
        }
        $this->setMessage('恭喜你，预约成功');
        return true;
    }

    /**
     * 修改项目订单信息
     * @param array $data
     * @return mixed
     */
    public function updProject(array $data)
    {
        $id = $data['id'];
        unset($data['sign'], $data['token'], $data['id']);
        $data['updated_at']     = time();
        $data['reservation_at'] = strtotime($data['reservation_at']);
        $data['status']         = '1';  // 修改数据后  状态值从新开始

        if (!$res = ProjectOrderRepository::getUpdId(['id' => $id],$data)){
            $this->setError('修改失败,请重试！');
            return false;
        }
        $this->setMessage('恭喜你，修改成功');
        return true;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function delProject(string $id)
    {
        if (!$ProjectInfo = ProjectOrderRepository::getOne(['id' => $id])){
            $this->setError('没有查找到该数据,请重试！');
            return false;
        }
        if (!$res = ProjectOrderRepository::getUpdId(['id' => $id],['status' => '9'])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功');
        return true;
    }
}
            