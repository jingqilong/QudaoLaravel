<?php
namespace App\Services\Member;


use App\Enums\GradeServiceEnum;
use App\Enums\MemberEnum;
use App\Enums\MemberGradeEnum;
use App\Repositories\MemberGradeDefineRepository;
use App\Repositories\MemberGradeRepository;
use App\Repositories\MemberGradeServiceRepository;
use App\Repositories\MemberGradeServiceViewRepository;
use App\Repositories\MemberServiceRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class GradeServiceService extends BaseService
{
    use HelpTrait;
    protected $auth;

    /**
     * EmployeeService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }

    /**
     * 给等级添加服务
     * @param $request
     * @return bool|null
     */
    public function gradeAddService($request)
    {
        if (!MemberGradeDefineRepository::exists(['iden' => $request['grade'],'status' => MemberGradeEnum::ENABLE])){
            $this->setError('会员等级不存在!');
            return false;
        }
        if (!$service = MemberServiceRepository::getOne(['id' => $request['service_id']])){
            $this->setError('服务不存在!');
            return false;
        }
        if (MemberGradeServiceRepository::exists(
            ['grade'         => $request['grade'],
            'service_id'    => $request['service_id'],])){
            $this->setError('此等级该服务已存在，请勿重复添加!');
            return false;
        }
        if (!MemberGradeServiceRepository::create([
            'grade'         => $request['grade'],
            'service_id'    => $request['service_id'],
            'status'        => $request['status'],
            'number'        => $request['number'],
            'cycle'         => $request['cycle'],
            'created_at'    => time(),
            'updated_at'    => time(),
        ])){
            $this->setError('给等级添加服务失败!');
            return false;
        }
        $this->setMessage('给等级添加服务成功！');
        return true;
    }

    /**
     * 删除等级中的服务
     * @param $id
     * @return bool
     */
    public function gradeDeleteService($id)
    {
        if (!MemberGradeServiceRepository::exists(['id' => $id])){
            $this->setError('记录不存在!');
            return false;
        }
        if (!MemberGradeServiceRepository::delete(['id' => $id])){
            $this->setError('服务删除失败!');
            return false;
        }
        $this->setMessage('服务删除成功！');
        return true;
    }

    /**
     * 修改
     * @param $request
     * @return bool
     */
    public function gradeEditService($request)
    {
        if (!$service = MemberServiceRepository::getOne(['id' => $request['service_id']])){
            $this->setError('服务不存在!');
            return false;
        }
        if (!MemberGradeServiceRepository::exists(['id' => $request['id']])){
            $this->setError('此条记录不存在!');
            return false;
        }
        if (!MemberGradeServiceRepository::getUpdId(
            ['id'           => $request['id']],
            [
            'service_id'    => $request['service_id'],
            'status'        => $request['status'],
            'number'        => $request['number'],
            'cycle'         => $request['cycle'],
            'updated_at'    => time(),
        ])){
            $this->setError('修改失败!');
            return false;
        }
        $this->setMessage('修改成功！');
        return true;
    }

    /**
     * 等级下服务详情列表
     * @param $grade
     * @return array|bool
     */
    public function gradeServiceDetail($grade){

        if (!MemberGradeDefineRepository::exists(['iden' => $grade,'status' => MemberGradeEnum::ENABLE])){
            $this->setError('会员等级不存在!');
            return false;
        }
        if (!$grade_list = MemberGradeServiceRepository::getAllList(['grade' => $grade],['id','grade','service_id','status','number','cycle'])){
            $this->setMessage('该等级下暂无服务');
            return [];
        }
        $service_list = MemberServiceRepository::getAllIndexService();
        #给服务名字加上父级名称
        foreach ($grade_list as &$value){
            if (isset($service_list[$value['service_id']])){
                $value['service_name'] = $service_list[$value['service_id']]['name'];
            }
        }
        $this->setMessage('获取成功！');
        return $grade_list;
    }

    /**
     * H5获取等级权益详情
     * @param $grade
     * @return array|bool|null
     */
    public function getGradeService($grade)
    {
        if (!MemberGradeDefineRepository::exists(['iden' => $grade,'status' => MemberGradeEnum::ENABLE])){
            $this->setError('会员等级不存在!');
            return false;
        }
        $where = ['grade' => $grade,'status' => GradeServiceEnum::USABLE];
        if (!$grade_list = MemberGradeServiceViewRepository::getAllList($where,['service_name','service_desc','number','cycle'])){
            $this->setMessage('该等级下暂无服务');
            return [];
        }
        $this->setMessage('获取成功！');
        return $grade_list;
    }

    /**
     * 获取等级卡片列表（前端）
     * @return array|null
     */
    public function getGradeCardList()
    {
        $column = ['iden','title','amount','image_id'];
        if (!$list = MemberGradeDefineRepository::getAllList(['status' => MemberGradeEnum::ENABLE,'is_buy' => MemberGradeEnum::CANBUY],$column)){
            $this->setMessage('暂无等级！');
            return [];
        }
        $list = ImagesService::getListImagesConcise($list,['image_id' => 'single'],true);
        foreach ($list as &$value){
            $value['service_count'] = 0;
            if ($service_count = MemberGradeServiceRepository::count(['grade' => $value['iden']])){
                $value['service_count'] = $service_count;
            }
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 获取成员等级申请详情
     * @param $select_grade
     * @return array|bool
     */
    public function getGradeApplyDetail($select_grade)
    {
        $member = $this->auth->user();
        if (!MemberGradeDefineRepository::exists(['iden' => $select_grade,'status' => MemberGradeEnum::ENABLE])){
            $this->setError('会员等级不存在!');
            return false;
        }
        $grade  = 0;
        if ($member_grade = MemberGradeRepository::getOne(['user_id' => $member->id,'status' => MemberEnum::PASS])){
            $grade = $member_grade['grade'];
        }
        $res = [
            'now_grade'         => $grade,
            'now_grade_title'   => MemberGradeDefineRepository::getLabelById($grade),
            'years_list'        => $select_grade == MemberGradeDefineRepository::YOUENJOY() ? [6] : [1,2,3,4,5]
        ];
        $this->setMessage('获取成功！');
        return $res;
    }
}
            