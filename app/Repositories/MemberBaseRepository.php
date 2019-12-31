<?php


namespace App\Repositories;


use App\Enums\MemberEnum;
use App\Models\MemberBaseModel;
use App\Repositories\Traits\RepositoryTrait;
use App\Traits\HelpTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MemberBaseRepository extends ApiRepository
{
    use RepositoryTrait;
    use HelpTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(MemberBaseModel $model)
    {
        $this->model = $model;
    }

    /**
     * OA 获取成员基础信息
     * @param $keywords
     * @param $is_home_detail
     * @param int $page
     * @param int $page_num
     * @param string $asc
     * @param array $where
     * @return bool|mixed|null
     */
    protected function searchMemberBase($keywords, $is_home_detail, int $page, int $page_num, string $asc, array $where)
    {
        $base_column = ['id','card_no','ch_name','en_name','avatar_id','sex','mobile','address','status','hidden','created_at'];
        if (!empty($is_home_detail)) $where['is_home_detail'] = $is_home_detail;
        if (!empty($keywords)){
            $keyword  = [$keywords => ['ch_name','en_name','card_no','mobile']];
            if (!$list = $this->search($keyword,$where,$base_column,$page,$page_num,'created_at',$asc)){
                return false;
            }
        }else{
            if (!$list = $this->getList($where,$base_column,'created_at',$asc,$page,$page_num)){
                return false;
            }
        }
        return $list;
    }

    /**
     * 添加新用户
     * @param $mobile
     * @param array $sub_data   附加信息
     * @return mixed
     */
    protected function addUser($mobile, $sub_data = [])
    {
        $user_data = [
            'card_no'       => $sub_data['card_no'] ?? $mobile,
            'mobile'        => $mobile,
            'created_at'    => time(),
            'updated_at'    => time(),
            'referral_code' => $this->getReferralCode()
        ];
        $user_data = array_merge($user_data,$sub_data);
        return $this->getAddId($user_data);
    }

    /**
     * 获取token
     * @param mixed $user 用户模型获取id
     * @return mixed
     */
    protected function getToken($user)
    {
        if (is_integer($user)){
            $user = $this->model->where(['id' => $user])->first();
        }
        if (! $token = Auth::guard('member_api')->fromUser($user)) {
            return false;
        }
        Auth::guard('member_api')->setToken($token);
        return $token;
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param array $account_password       包含账户和密码，账户可以为会员卡号、邮箱、手机号
     * @return array|JsonResponse|string
     */
    protected function login (array $account_password = ['card_no' => '','password' => '']){
        $where = $account_password;
        unset($where['password']);
        if (!$user = $this->model->where($where)->first()){
            return ['code' => 100, 'message' => '账户不存在'];
        }
        if (!Hash::check($account_password['password'],$user->password)){
            return ['code' => 100, 'message' => '密码不正确'];
        }

        if (! $token = Auth::guard('member_api')->fromUser($user)) {
            return ['code' => 100, 'message' => 'token获取失败'];
        }
        Auth::guard('member_api')->setToken($token);
        return $token;
    }


    /**
     * Get the authenticated User.
     *
     * @return mixed
     */
    protected function getUser()
    {
        $auth = Auth::guard('member_api');
        return $auth->user();
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return bool
     */
    protected function logout()
    {
        $auth = Auth::guard('member_api');
        $auth->logout();

        return true;
    }

    /**
     * Refresh a token.
     *
     * @return mixed
     */
    protected function refresh()
    {
        $auth = Auth::guard('member_api');
        return $auth->refresh();
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return mixed
     */
    protected function respondWithToken($token)
    {
        $auth = Auth::guard('member_api');
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $auth->factory()->getTTL() * 60
        ];
    }

    /**
     * 生成邀请码
     * @param int $len
     * @return string
     */
    protected function getReferralCode($len = 8){
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        if ($this->exists(['referral_code' => $str])){
            return self::getReferralCode($len);
        }
        return $str;
    }
    }
            