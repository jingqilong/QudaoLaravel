<?php
namespace App\Services\Project;


use App\Enums\MemberEnum;
use App\Enums\ProjectEnum;
use App\Repositories\MemberRepository;
use App\Repositories\ProjectOrderRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
use Illuminate\Support\Facades\Auth;

class ProjectService extends BaseService
{

    protected $auth;

    /**
     * ProjectService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }
    /**
     * 获取项目对接订单列表  （前端使用）
     * @return mixed
     */
    public function getProjectList()
    {
        $user = $this->auth->user();
        $where = ['user_id' => $user->m_id,'deleted_at' => 0];
        if (!$list = ProjectOrderRepository::getList($where,['*'],'created_at','desc')){
            $this->setMessage('没有数据！');
            return [];
        }
        $list['status_name']       =   ProjectEnum::getStatus($list['status']);
        $list['reservation_at']    =   date('Y-m-d H:m:s',$list['reservation_at']);
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
        $user = $this->auth->user();
        if (!$list = ProjectOrderRepository::getOne(['id' => $id,'user_id' => $user->m_id],['id','name','mobile','project_name','remark','status'])){
            $this->setError('暂无数据!');
            return false;
        }
        $list['status_name']       =   ProjectEnum::getStatus($list['status']);
        $list['reservation_at']    =   date('Y-m-d H:m:s',$list['reservation_at']);
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
        $user = $this->auth->user();
        $member_id = $user->m_id;
        $add_arr = [
            'user_id'           => $member_id,
            'name'              => $data['name'],
            'mobile'            => $data['mobile'],
            'project_name'      => $data['project_name'],
            'remark'            => $data['remark'],
            'reservation_at'    => strtotime($data['reservation_at']),
        ];
        if (ProjectOrderRepository::getOne($add_arr)){
            $this->setError('信息已存在');
            return false;
        }
        $add_arr['user_id']      =   $member_id;
        $add_arr['created_at']   =   time();
        $add_arr['status']       =   ProjectEnum::SUBMIT;
        if (!ProjectOrderRepository::getAddId($add_arr)){
            $this->setError('预约失败!');
            return false;
        }
        $this->setMessage('预约成功!');
        return true;


    }

    /**
     * 修改项目订单信息
     * @param array $data
     * @return mixed
     */
    public function updProject(array $data)
    {
        $add_arr = [
            'id'                => $data['id'],
            'name'              => $data['name'],
            'mobile'            => $data['mobile'],
            'project_name'      => $data['project_name'],
            'remark'            => $data['remark'],
            'reservation_at'    => strtotime($data['reservation_at']),
        ];
        if (ProjectOrderRepository::getOne($add_arr)){
            $this->setError('信息已存在');
            return false;
        }
        if (!ProjectOrderRepository::getUpdId(['id' => $data['id']],$add_arr)){
            $this->setError('修改失败,请重试！');
            return false;
        }
        $this->setMessage('恭喜你，修改成功!');
        return true;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function delProject(string $id)
    {
        if (!$ProjectInfo = ProjectOrderRepository::getOne(['id' => $id])){
            $this->setError('暂无数据!');
            return false;
        }
        if ($ProjectInfo['deleted'] !== 0){
            $this->setError('项目已被删除！请勿重新操作');
            return false;
        }
        if (!ProjectOrderRepository::getUpdId(['id' => $id],['deleted' => 0])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功');
        return true;
    }
}
            