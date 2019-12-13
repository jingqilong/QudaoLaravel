<?php
namespace App\Services\Member;


use App\Repositories\MemberGradeServiceRepository;
use App\Repositories\MemberRepository;
use App\Repositories\OaGradeViewRepository;
use App\Repositories\OaMemberRepository;
use App\Repositories\MemberServiceRepository;
use App\Repositories\MemberSpecifyViewRepository;
use App\Services\BaseService;
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
            'cycle'         => $request['cycle'] * 86400,
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
        if (!$grade_list = MemberGradeServiceRepository::getList(['grade' => $grade],['id','grade','service_id','status','number','cycle'])){
            $this->setMessage('该等级下暂无服务');
            return [];
        }
        $service_ids = array_column($grade_list,'service_id');
        if (!$service_list = MemberServiceRepository::getList(['id' => ['in',$service_ids]],['id','name'])){
            $this->setError('获取失败！');
            return false;
        }
        foreach ($grade_list as &$value){
            if ($service = $this->searchArray($service_list,'id',$value['service_id'])){
                $value['service_name'] = reset($service)['name'];
            }
        }
        $this->setMessage('获取成功！');
        return $grade_list;
    }


    /**
     * 添加会员可查看成员
     * @param $request
     * @return bool|null
     */
    public function addViewMember($request){
        if ($request['-'] != 0){
            if (!MemberRepository::exists(['m_id' => $request['member_id']])){
                $this->setError('成员不存在！');
                return false;
            }
        }
        if (isset($request['view_user_id'])){
            $where = ['m_id' => $request['view_user_id']];
        }else{
            $view_user = $request['view_user'];
            //兼容用户名、手机号、邮箱、ID添加，
            $mobile_regex = '/^(1(([35789][0-9])|(47)))\d{8}$/';
            $email_regex  = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
            $num_regx     = '/\d+$/';
            $user_iden    = '';
            if (preg_match($mobile_regex, $view_user)) {
                $user_iden = 'm_phone';
            }
            if (preg_match($email_regex, $view_user)) {
                $user_iden = 'm_email';
            }
            if (preg_match($num_regx, $view_user) && !preg_match($mobile_regex, $view_user)) {
                $user_iden = 'm_num';
            }
            if (empty($user_iden)){
                $this->setError('可查看成员条件格式不正确！');
                return false;
            }
            $where = [$user_iden => $view_user];
        }
        if (!$view_user_id = OaMemberRepository::getField($where,'m_id')){
            $this->setError('可查看成员不存在！');
            return false;
        }
        if (MemberSpecifyViewRepository::exists([
            'user_id'       => $request['member_id'],
            'view_user_id'  => $view_user_id,])){
            $this->setError('可查看成员已存在，请勿重复添加！');
            return false;
        }
        if (!$id = MemberSpecifyViewRepository::getAddId([
            'user_id'       => $request['member_id'],
            'view_user_id'  => $view_user_id,
            'created_at'    => time()
        ])){
            $this->setError('添加失败！');
            return false;
        }
        $this->setMessage('添加成功！');
        return true;
    }

    /**
     * 软删除可查看成员
     * @param $id
     * @return bool
     */
    public function deleteViewMember($id){
        if(!MemberSpecifyViewRepository::exists(['id' => $id, 'deleted_at' => 0])){
            $this->setError('记录不存在或已被删除！');
            return false;
        }
        if (!MemberSpecifyViewRepository::delete(['id' => $id])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 恢复可查看成员
     * @param $id
     * @return bool
     */
    public function restoreViewMember($id){
        if(!MemberSpecifyViewRepository::exists(['id' => $id])){
            $this->setError('记录不存在！');
            return false;
        }
        if(MemberSpecifyViewRepository::exists(['id' => $id, 'deleted_at' => 0])){
            $this->setError('记录已恢复，请勿重复操作！');
            return false;
        }
        if (!MemberSpecifyViewRepository::getUpdId(['id' => $id],['deleted_at' => 0])){
            $this->setError('恢复失败！');
            return false;
        }
        $this->setMessage('恢复成功！');
        return true;
    }


    /**
     * 添加等级可查看成员
     * @param array $data
     * @return bool
     */
    public function addGradeView(array $data)
    {
        $add_arr['grade']         = $data['grade'];
        $add_arr['type']          = $data['type'];
        $add_arr['value']         = $data['value'];
        if (OaGradeViewRepository::exists($add_arr)){
            $this->setError('信息已存在!');
            return false;
        }
        $add_arr['created_at']    = time();
        $add_arr['updated_at']   = time();
        if (!OaGradeViewRepository::getAddId($add_arr)){
            $this->setError('添加失败!');
            return false;
        }
        $this->setMessage('添加成功！');
        return true;
    }
}
            