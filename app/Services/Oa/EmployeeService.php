<?php
namespace App\Services\Oa;


use App\Repositories\CommonImagesRepository;
use App\Repositories\OaAdminPermissionsRepository;
use App\Repositories\OaAdminRolesRepository;
use App\Repositories\OaDepartmentRepository;
use App\Repositories\OaEmployeeRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        $user = $this->returnUserInfo($user->toArray());
        $this->setMessage('登录成功！');
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
        $employee           = OaEmployeeRepository::getUser()->toArray();
        $this->setMessage('获取成功！');
        return $this->returnUserInfo($employee);
    }


    /**
     * 权限管理中的获取用户列表
     * @return mixed
     */
    public function getPermUserList($request){
        $keywords       = $request['keywords'] ?? null;
        $department_id  = $request['department_id'] ?? null;
        $created_at_sort= $request['created_at_sort'] ?? 1;
        $column         = ['id', 'username', 'real_name','department_id', 'mobile', 'email','work_title', 'status', 'role_ids', 'permission_ids', 'created_at', 'updated_at'];
        $where          = ['id' => ['>',0]];
        $sort           = ['id','created_at'];
        $asc            = $created_at_sort == 1 ? ['desc','desc'] : ['asc','asc'];
        if (!is_null($department_id)){
            $where['department_id'] = $department_id;
        }
        if (!empty($keywords)){
            $keyword = [$keywords => ['username','real_name','mobile','email','work_title']];
            if (!$user_list = OaEmployeeRepository::search($keyword,$sort,$asc)){
                $this->setError('获取失败!');
                return false;
            }
        }else{
            if (!$user_list = OaEmployeeRepository::getList($where,$column,$sort,$asc)){
                $this->setError('获取失败!');
                return false;
            }
        }
        $user_list = $this->removePagingField($user_list);
        if (empty($user_list['data'])){
            $this->setMessage('暂无数据!');
            return $user_list;
        }
        //获取部门
        $department_ids = array_column($user_list['data'],'department_id');
        $department_list = OaDepartmentRepository::getAllList(['id' => ['in',$department_ids]],['id','name']);
        //获取角色和权限
        list($role_ids,$permission_ids) = $this->getArrayIds($user_list['data'],['role_ids','permission_ids']);
        $role_list          = OaAdminRolesRepository::getAssignList($role_ids,['id','name']);
        $role_list          = OaAdminRolesRepository::createArrayIndex($role_list,'id');
        $permission_list    = OaAdminPermissionsRepository::getAssignList($permission_ids,['id','name']);
        $permission_list    = OaAdminPermissionsRepository::createArrayIndex($permission_list,'id');
        foreach ($user_list['data'] as &$value){
            $value['roles'] = [];
            $role_ids       = explode(',',trim($value['role_ids'],','));
            foreach ($role_ids as $id){
                if (isset($role_list[$id]))
                $value['roles'][] = $role_list[$id];
            }
            $value['permissions']   = [];
            $permission_ids         = explode(',',trim($value['permission_ids'],','));
            foreach ($permission_ids as $id){
                if (isset($permission_list[$id]))
                    $value['permissions'][] = $permission_list[$id];
            }
            $value['department_name'] = '';
            if ($department = $this->searchArray($department_list,'id',$value['department_id'])){
                $value['department_name'] = reset($department)['name'];
            }
            $value['created_at'] = date('Y-m-d H:m:s',$value['created_at']);
            $value['updated_at'] = date('Y-m-d H:m:s',$value['updated_at']);
            unset($value['role_ids'],$value['permission_ids']);
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
        if (!OaDepartmentRepository::exists(['id' => $request['department_id']])){
            $this->setError('部门不存在！');
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
        if ($roles = $request['role_ids'] ?? ''){
            $arr_role = explode(',',$roles);
            if (count($arr_role) != OaAdminRolesRepository::count(['id' => ['in',$arr_role]])){
                $this->setError('包含了不存在的角色！');
                return false;
            }
        }
        if ($permission_ids = $request['permission_ids'] ?? ''){
            $perm_ids = explode(',',$permission_ids);
            if (count($perm_ids) != OaAdminPermissionsRepository::count(['id' => ['in',$perm_ids]])){
                $this->setError('包含了不存在的权限！');
                return false;
            }
        }
        $add_user = [
            'username'      => $request['username'],
            'password'      => Hash::make($request['password']),
            'real_name'     => $request['real_name'],
            'department_id' => $request['department_id'],
            'gender'        => $request['gender'],
            'mobile'        => $request['mobile'],
            'email'         => $request['email'],
            'work_title'    => $request['work_title'],
            'birth_date'    => isset($request['birth_date']) ? strtotime($request['birth_date']) : null,
            'avatar_id'     => $request['avatar_id'],
            'status'        => $request['status'] ?? 0,
            'role_ids'      => empty($roles) ? '' : ','.$roles.',',
            'permission_ids'=> empty($permission_ids) ? '' : ','.$permission_ids.',',
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
        if (!OaDepartmentRepository::exists(['id' => $request['department_id']])){
            $this->setError('部门不存在！');
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
        if ($roles = $request['role_ids'] ?? ''){
            $arr_role = explode(',',$roles);
            if (count($arr_role) != OaAdminRolesRepository::count(['id' => ['in',$arr_role]])){
                $this->setError('包含了不存在的角色！');
                return false;
            }
        }
        if ($permission_ids = $request['permission_ids'] ?? ''){
            $perm_ids = explode(',',$permission_ids);
            if (count($perm_ids) != OaAdminPermissionsRepository::count(['id' => ['in',$perm_ids]])){
                $this->setError('包含了不存在的权限！');
                return false;
            }
        }
        $upd_arr = [
            'username'      => $request['username'],
            'real_name'     => $request['real_name'],
            'department_id' => $request['department_id'],
            'gender'        => $request['gender'],
            'mobile'        => $request['mobile'],
            'email'         => $request['email'],
            'work_title'    => $request['work_title'],
            'birth_date'    => isset($request['birth_date']) ? strtotime($request['birth_date']) : null,
            'avatar_id'     => $request['avatar_id'],
            'role_ids'      => empty($roles) ? '' : ','.$roles.',',
            'permission_ids'=> empty($permission_ids) ? '' : ','.$permission_ids.',',
            'updated_at'    => time(),
        ];
        if (isset($request['status'])){
            $upd_arr['status'] = $request['status'];
        }
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
        $column = ['id','username','real_name','gender','note','work_title','birth_date','status','email','department_id','mobile','avatar_id','role_ids','permission_ids','created_at','updated_at'];
        if (!$user = OaEmployeeRepository::getOne(['id' => $employee_id],$column)){
            $this->setError('员工信息不存在！');
            return false;
        }
        $user['avatar_url'] = url('images/default_avatar.jpg');
        if (!empty($user['avatar_id'])){
            $user = ImagesService::getOneImagesConcise($user,['avatar_id' => 'single']);
        }
        $department = OaDepartmentRepository::getField(['id'=>$user['department_id']],'name');
        $user['department'] = $department ? $department : '';
        $user['birth_date'] = $user['birth_date'] ? date('Y-m-d',$user['birth_date']) : '';
        $user['role_ids']   = trim($user['role_ids'],',');
        $user['permission_ids']   = trim($user['permission_ids'],',');
        $user['roles'] = [];
        if (!empty($user['role_ids'])){
            $role_ids = explode(',',trim($user['role_ids'],','));
            if ($roles = OaAdminRolesRepository::getAllList(['id' => ['in',$role_ids]],['id','name'])){
                $user['roles']  = $roles;
            }
        }
        $user['permissions'] = [];
        if (!empty($user['permission_ids'])){
            $permission_ids = explode(',',trim($user['permission_ids'],','));
            if ($permission = OaAdminPermissionsRepository::getAllList(['id' => ['in',$permission_ids]],['id','name'])){
                $user['permissions']    = $permission;
            }
        }
        $user['created_at'] = date('Y-m-d H:i:s',$user['created_at']);
        $user['updated_at'] = date('Y-m-d H:i:s',$user['updated_at']);
        $this->setMessage('获取成功！');
        return $user;
    }

    /**
     * OA获取列表中操作人名称
     * @param $list
     * @param array $columns        ['字段名' => '别名']
     * @return mixed
     */
    public static function getListOperationByName($list,$columns = ['created_by' => 'created_by_name','updated_by' => 'updated_by_name']){
        if (empty($columns)){
            return $list;
        }
        $employee_ids   = [0];
        foreach ($columns as $column => $column_name){
            $column_employee_ids = array_column($list,$column);
            $employee_ids = array_merge($employee_ids,$column_employee_ids);
        }
        $employee_list  = OaEmployeeRepository::getAllList(['id' => ['in',$employee_ids]]);
        foreach ($list as &$datum){
            foreach ($columns as $column => $alias){
                $datum[$alias] = '';
                if ($created_by = self::searchArrays($employee_list,'id',$datum[$column])){
                    $datum[$alias]   = reset($created_by)['real_name'];
                }
            }
        }
        return $list;
    }


    /**
     * 编辑个人信息
     * @param $request
     * @return mixed
     */
    public function editPersonalInfo($request){
        $employee = $this->auth->user();
        $upd_arr = [
            'real_name'     => $request['real_name'],
            'gender'        => $request['gender'],
            'avatar_id'     => $request['avatar_id'],
            'birth_date'    => isset($request['birth_date']) ? strtotime($request['birth_date']) : null,
            'updated_at'    => time()
        ];
        if (!OaEmployeeRepository::getUpdId(['id' => $employee->id],$upd_arr)){
            $this->setError('编辑失败！');
            return false;
        }
        $column             = ['id','username','real_name','department_id','gender','mobile','email','work_title','avatar_id','birth_date','role_ids','permission_ids'];
        $user               = OaEmployeeRepository::getOne(['id' => $employee->id],$column);
        $this->setMessage('编辑成功！');
        return $this->returnUserInfo($user);
    }

    /**
     * 修改密码
     * @param $request
     * @param $employee_id
     * @return bool
     */
    public function editPersonalPassword($request, $employee_id){
        #此处做限制，每天只能修改密码指定次数
        $key    = md5('oa_employee_self_edit_password'.$employee_id);
        $count  = 1;//今日修改次数初始化
        $number = config('common.employee_self_edit_password_number',3);//获取每日修改密码上限次数
        if (Cache::has($key)){
            $count = Cache::get($key);
            if ($count >= $number){
                $this->setError('今日修改密码'.$number.'次机会已用完！');
                return false;
            }
            ++$count;
        }
        if ($request['password'] !== $request['confirm_password']){
            $this->setError('两次密码不一致！');
            return false;
        }
        $upd = [
            'password'  => Hash::make($request['password']),
            'updated_at'=> time()
        ];
        if (!OaEmployeeRepository::getUpdId(['id' => $employee_id],$upd)){
            $this->setError('修改密码失败！');
            return false;
        }
        Cache::forget($key);
        Cache::add($key,$count,strtotime('23:59:59')-time());
        $this->setMessage('修改密码成功！');
        return true;
    }

    /**
     * 使用旧密码修改密码
     * @param $request
     * @return bool
     */
    public function editPassword($request){
        $employee = $this->auth->user();
        if (!Hash::check($request['old_password'],$employee->password)){
            $this->setError('旧密码输入有误！');
            return false;
        }
        return $this->editPersonalPassword($request,$employee->id);
    }


    /**
     * 员工修改绑定手机号
     * @param $request
     * @return bool
     */
    public function editBindMobile($request){
        $employee = $this->auth->user();
        #此处做限制，每天修改手机号密码错误指定次数
        $key    = md5('oa_employee_self_edit_mobile'.$employee->id);
        $count  = 1;//今日修改次数初始化
        $number = config('common.employee_self_edit_mobile_error_number');//获取每日修改手机号密码错误上限次数
        if (Cache::has($key)){
            $count = Cache::get($key);
            if ($count >= $number){
                $this->setError('密码错误'.$number.'次，已达今日上限！');
                return false;
            }
            ++$count;
        }
        if (!Hash::check($request['password'],$employee->password)){
            Cache::forget($key);
            Cache::add($key,$count,strtotime('23:59:59')-time());
            $this->setError('密码错误！');
            return false;
        }
        if (OaEmployeeRepository::exists(['mobile' => $request['mobile'],'id' => ['<>',$employee->id]])){
            $this->setError('该手机号已被绑定！');
            return false;
        }
        $upd = [
            'mobile'    => $request['mobile'],
            'updated_at'=> time()
        ];
        if (!OaEmployeeRepository::getUpdId(['id' => $employee->id],$upd)){
            $this->setError('修改手机号失败！');
            return false;
        }
        $this->setMessage('修改手机号成功！');
        return true;
    }

    /**
     * 员工修改绑定手机号
     * @param $request
     * @return bool
     */
    public function editBindEmail($request){
        $employee = $this->auth->user();
        #此处做限制，每天修改邮箱密码错误指定次数
        $key    = md5('oa_employee_self_edit_email'.$employee->id);
        $count  = 1;//今日修改次数初始化
        $number = config('common.employee_self_edit_email_error_number');//获取每日修改邮箱密码错误上限次数
        if (Cache::has($key)){
            $count = Cache::get($key);
            if ($count >= $number){
                $this->setError('密码错误'.$number.'次，已达今日上限！');
                return false;
            }
            ++$count;
        }
        if (!Hash::check($request['password'],$employee->password)){
            Cache::forget($key);
            Cache::add($key,$count,strtotime('23:59:59')-time());
            $this->setError('密码错误！');
            return false;
        }
        if (OaEmployeeRepository::exists(['email' => $request['email'],'id' => ['<>',$employee->id]])){
            $this->setError('该邮箱已被绑定！');
            return false;
        }
        $upd = [
            'email'         => $request['email'],
            'updated_at'    => time()
        ];
        if (!OaEmployeeRepository::getUpdId(['id' => $employee->id],$upd)){
            $this->setError('修改邮箱失败！');
            return false;
        }
        $this->setMessage('修改邮箱成功！');
        return true;
    }

    /**
     * 返回用户信息
     * @param $user
     * @return array
     */
    public function returnUserInfo($user){
        if (empty($user)){
            return [];
        }
        $column             = ['id','username','real_name','department_id','gender','mobile','email','work_title','avatar_id','birth_date','role_ids','permission_ids'];
        $user               = Arr::only($user,$column);
        $user['avatar_url'] = CommonImagesRepository::getField(['id' => $user['avatar_id']],'img_url');
        $user['avatar_url'] = $user['avatar_url'] ?? url('images/default_avatar.jpg');
        $user['birthday']   = empty($user['birth_date']) ? '' : date('Y-m-d',$user['birth_date']);
        $user['birth_date'] = empty($user['birth_date']) ? '' : date('m月d日',$user['birth_date']);
        $user['department'] = OaDepartmentRepository::getField(['id'=>$user['department_id']],'name');
        $user['roles']      = array_column(OaAdminRolesRepository::getAssignList([$user['role_ids']],['name']),'name');
        $user['permissions']= array_column(OaAdminPermissionsRepository::getAssignList([$user['permission_ids']],['name']),'name');
        unset($user['role_ids'],$user['department_id'],$user['permission_ids']);
        return $user;
    }
}
            