<?php
namespace App\Services\Oa;


use App\Repositories\OaAdminPermissionsRepository;
use App\Repositories\OaAdminRolesRepository;
use App\Repositories\OaDepartmentRepository;
use App\Repositories\OaEmployeeRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployeeService extends BaseService
{
    use HelpTrait;
    protected $auth;

    /**
     * EmployeeService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('oa_api');
    }

    /**
     * 用户登录，返回用户信息和TOKEN
     * @param $account
     * @param $password
     * @return mixed|string
     */
    public function login($account, $password){
        //兼容用户名登录、手机号登录、邮箱登录
        $mobile_regex = '/^(1(([35789][0-9])|(47)))\d{8}$/';
        $email_regex  = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
        $account_type = 'username';
        if (preg_match($mobile_regex, $account)) {
            $account_type = 'mobile';
        }
        if (preg_match($email_regex, $account)) {
            $account_type = 'email';
        }

        if (!OaEmployeeRepository::exists([$account_type => $account])){
            $this->setError('用户不存在！');
            return false;
        }
        $token = OaEmployeeRepository::login([$account_type => $account, 'password' => $password]);
        if (is_array($token)){
            $this->setError($token['message']);
            return false;
        }
        $user = $this->auth->user();
        if ($user->status == 1){
            $this->setError('您的账户已被管理员禁用！');
            return false;
        }
        $user = $user->toArray();
        if (empty($user['head_portrait'])){
            $user['head_portrait'] = url('images/default_avatar.jpg');
        }
        $user['department'] = OaDepartmentRepository::getField(['id'=>$user['department_id']],'name');
        $user['roles'] = [];
        if (!empty($user['role_id'])){
            $role_ids = explode(',',$user['role_id']);
            if ($roles = OaAdminRolesRepository::getList(['id' => ['in',$role_ids]],['name'])){
                $user['roles'] = array_column($roles,'name');
            }
        }
        if (!empty($user['permissions'])){
            $permission_ids = explode(',',$user['permissions']);
            if ($permission = OaAdminPermissionsRepository::getList(['id' => ['in',$permission_ids]],['name'])){
                $user['permissions'] = array_column($permission,'name');
            }
        }else{
            $user['permissions'] = [];
        }
        $user['created_at'] = date('Y-m-d H:i:s',$user['created_at']);
        $user['updated_at'] = date('Y-m-d H:i:s',$user['updated_at']);
        $this->setMessage('登录成功！');
        unset($user['role_id'],$user['status'],$user['note'],$user['department_id']);
        return ['user' => $user, 'token' => $token];
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @param $token
     * @return bool
     */
    public function logout($token)
    {
        if (OaEmployeeRepository::logout($token)){
            return true;
        }
        return false;
    }

    /**
     * Refresh a token.
     *
     * @param $token
     * @return mixed
     */
    public function refresh($token){
        if ($token = OaEmployeeRepository::refresh($token)){
            return $token;
        }
        return false;
    }



    /**
     * 员工本人信息
     * @return mixed
     */
    public function getUserInfo()
    {
        return OaEmployeeRepository::getUser();
    }


    /**
     * 权限管理中的获取用户列表
     * @param $page
     * @param $pageNum
     * @return mixed
     */
    public function getPermUserList($page,$pageNum){
        $column = ['id', 'username', 'real_name', 'mobile', 'email', 'status', 'role_id', 'permissions', 'created_at', 'updated_at'];
        if (!$user_list = OaEmployeeRepository::getList(['id' => ['>',0]],$column,'id','asc',$page,$pageNum)){
            $this->setError('获取失败!');
            return false;
        }
        $user_list = $this->removePagingField($user_list);
        if (empty($user_list['data'])){
            $this->setMessage('暂无数据!');
            return $user_list;
        }
        foreach ($user_list['data'] as &$value){
            $role_info = OaAdminRolesRepository::getOne(['id' => $value['role_id'] ?? 0]);
            $value['role_name'] = $role_info['name'] ?? '-';
            $value['role_slug'] = $role_info['slug'] ?? '-';
            $permissions = $value['permissions'] ?? '';
            $arr_perm = explode(',',$permissions);
            $value['perm_name'] = [];
            $value['perm_slug'] = [];
            if ($arr_perm){
                $user_permission = OaAdminPermissionsRepository::getList(['id' => ['in', $arr_perm]]);
                $value['perm_name'] = array_column($user_permission,'name');
                $value['perm_slug'] = array_column($user_permission,'slug');
            }
            $value['created_at'] = date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at'] = date('Y-m-d H:m:s',$value['updated_at']);
            unset($value['role_id'],$value['permissions']);
        }
        $this->setMessage('获取成功！');
        return $user_list;
    }

    /**
     * 权限管理模块添加用户
     * @param $request
     * @return bool
     */
    public function addPermUser($request)
    {
        if (OaEmployeeRepository::exists(['username' => $request['username']])){
            $this->setError('用户名已存在！');
            return false;
        }
        if (OaEmployeeRepository::exists(['email' => $request['email']])){
            $this->setError('邮箱已存在！');
            return false;
        }
        if (OaEmployeeRepository::exists(['mobile' => $request['mobile']])){
            $this->setError('手机已存在！');
            return false;
        }
        if ($roles = $request['roles'] ?? ''){
            $arr_role = explode(',',$roles);
            if (count($arr_role) != OaAdminRolesRepository::count(['id' => $arr_role])){
                $this->setError('包含了不存在的角色！');
                return false;
            }
        }
        if ($permission_ids = $request['permission_ids'] ?? ''){
            $perm_ids = explode(',',$permission_ids);
            if (count($perm_ids) != OaAdminPermissionsRepository::count(['id' => $perm_ids])){
                $this->setError('包含了不存在的权限！');
                return false;
            }
        }
        $add_user = [
            'username'      => $request['username'],
            'password'      => Hash::make($request['password']),
            'real_name'     => $request['real_name'],
            'mobile'        => $request['mobile'],
            'email'         => $request['email'],
            'head_portrait' => $request['head_portrait'],
            'status'        => 0,
            'role_id'       => $roles,
            'permissions'   => $permission_ids,
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (!OaEmployeeRepository::getAddId($add_user)){
            $this->setError('用户添加失败！');
            return false;
        }
        $this->setMessage('用户添加成功！');
        return true;
    }

    /**
     * 禁用或开启员工
     * @param $employee_id
     * @return bool
     */
    public function isDisabled($employee_id)
    {
        $current_employee = Auth::guard('oa_api')->user();
        if ($current_employee->id == $employee_id){
            $this->setError('不能操作您自己的账户！');
            return false;
        }
        if (!$employee = OaEmployeeRepository::getOne(['id' => $employee_id])){
            $this->setError('员工信息不存在！');
            return false;
        }
        if ($employee['status'] == 0){
            if (OaEmployeeRepository::getUpdId(['id' => $employee_id],['status' => 1,'updated_at' => time()])){
                $this->setMessage('禁用成功！');
                return true;
            }
        }else{
            if (OaEmployeeRepository::getUpdId(['id' => $employee_id],['status' => 0,'updated_at' => time()])){
                $this->setMessage('开启成功！');
                return true;
            }
        }
        $this->setError('操作失败！');
        return false;
    }

    /**
     * 删除成功！
     * @param $employee_id
     * @return bool
     */
    public function deleteUser($employee_id)
    {
        $current_employee = Auth::guard('oa_api')->user();
        if ($current_employee->id == $employee_id){
            $this->setError('不能操作您自己的账户！');
            return false;
        }
        if (!OaEmployeeRepository::exists(['id' => $employee_id])){
            $this->setError('员工信息不存在！');
            return false;
        }
        if ($employee_id == 1){
            $this->setError('此员工不能删除！');
            return false;
        }
        if (OaEmployeeRepository::delete(['id' => $employee_id])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }

    /**
     * 修改员工信息
     * @param $request
     * @return bool
     */
    public function editPermUser($request)
    {
        if (!$employee = OaEmployeeRepository::getModel()->where(['id' => $request['id']])->first()){
            $this->setError('员工信息不存在！');
            return false;
        }
        if (OaEmployeeRepository::exists(['id' => ['<>',$request['id']],'username' => $request['username']])){
        $this->setError('用户名已存在！');
        return false;
    }
        if (OaEmployeeRepository::exists(['id' => ['<>',$request['id']],'email' => $request['email']])){
            $this->setError('邮箱已存在！');
            return false;
        }
        if (OaEmployeeRepository::exists(['id' => ['<>',$request['id']],'mobile' => $request['mobile']])){
            $this->setError('手机已存在！');
            return false;
        }
        $employee = $employee->makeVisible('password')->toArray();
        $new_password = '';
        if (isset($request['old_password'])&&isset($request['new_password'])){
            if (!Hash::check($request['old_password'],$employee['password'])){
                $this->setError('旧密码错误！');
                return false;
            }
            $new_password = Hash::make($request['new_password']);
        }
        if ($roles = $request['roles'] ?? ''){
            $arr_role = explode(',',$roles);
            if (count($arr_role) != OaAdminRolesRepository::count(['id' => $arr_role])){
                $this->setError('包含了不存在的角色！');
                return false;
            }
        }
        if ($permission_ids = $request['permission_ids'] ?? ''){
            $perm_ids = explode(',',$permission_ids);
            if (count($perm_ids) != OaAdminPermissionsRepository::count(['id' => $perm_ids])){
                $this->setError('包含了不存在的权限！');
                return false;
            }
        }
        $upd_arr = [
            'username'      => $request['username'],
            'real_name'     => $request['real_name'],
            'mobile'        => $request['mobile'],
            'email'         => $request['email'],
            'head_portrait' => $request['head_portrait'],
            'role_id'       => $roles,
            'permissions'   => $permission_ids,
            'updated_at'    => time(),
        ];
        if (!empty($new_password)){
            $upd_arr['password'] = $new_password;
        }
        if (!OaEmployeeRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('修改失败！');
            return false;
        }
        $this->setMessage('修改成功！');
        return true;
    }

    /**
     * 获取员工信息
     * @param $employee_id
     * @return mixed
     */
    public function getEmployeeDetails($employee_id){
        $column = ['id','username','real_name','gender','note','work_title','birth_date','id_card','status','email','department_id','mobile','head_portrait','role_id','permissions','created_at','updated_at'];
        if (!$user = OaEmployeeRepository::getOne(['id' => $employee_id],$column)){
            $this->setError('员工信息不存在！');
            return false;
        }
        $user['department'] = OaDepartmentRepository::getField(['id'=>$user['department_id']],'name');
        $user['role_ids'] = '';
        if (!empty($user['role_id'])){
            $role_ids = explode(',',$user['role_id']);
            if ($roles = OaAdminRolesRepository::getList(['id' => ['in',$role_ids]],['id','name'])){
                $role_ids       = array_column($roles,'id');
                $user['role_ids']  = implode($role_ids,',');
            }
        }
        if (!empty($user['permissions'])){
            $permission_ids = explode(',',$user['permissions']);
            if ($permission = OaAdminPermissionsRepository::getList(['id' => ['in',$permission_ids]],['id','name'])){
                $permission_ids         = array_column($permission,'id');
                $user['permission_ids']    = implode($permission_ids,',');
            }
        }else{
            $user['permission_ids'] = '';
        }
        $user['created_at'] = date('Y-m-d H:i:s',$user['created_at']);
        $user['updated_at'] = date('Y-m-d H:i:s',$user['updated_at']);
        $this->setMessage('获取成功！');
        return $user;
    }
}
            