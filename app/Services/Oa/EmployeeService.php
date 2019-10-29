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
            return '用户不存在！';
        }
        $token = OaEmployeeRepository::login([$account_type => $account, 'password' => $password]);
        if (is_array($token)){
            return $token['message'];
        }
        $user = $this->auth->user();
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
        unset($user_list['first_page_url'], $user_list['from'],
            $user_list['from'], $user_list['last_page_url'],
            $user_list['next_page_url'], $user_list['path'],
            $user_list['prev_page_url'], $user_list['to']);
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
            if (count($arr_role) != count(OaAdminRolesRepository::getList(['id' => $arr_role]))){
                $this->setError('包含了不存在的角色！');
                return false;
            }
        }
        if ($permission_ids = $request['permission_ids'] ?? ''){
            $perm_ids = explode(',',$permission_ids);
            if (count($perm_ids) != count(OaAdminPermissionsRepository::getList(['id' => $perm_ids]))){
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
            'status'        => 0,
            'role_id'       => $request['roles'],
            'permissions'   => $request['permission_ids'],
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
}
            