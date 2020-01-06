<?php


namespace App\Repositories;


use App\Enums\MemberEnum;
use App\Models\MemberBaseModel;
use App\Repositories\Traits\RepositoryTrait;
use App\Services\Common\ImagesService;
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
     * 获取OA列表
     * @param $keywords
     * @param $where
     * @param $column
     * @param $page
     * @param $page_num
     * @param $order
     * @param $asc
     * @return bool|mixed|null
     */
    protected function getMemberList($keywords, $where, $column, $page, $page_num, $order, $asc)
    {
        $where['id'] = ['>',1];
        if (!empty($keywords)){
            $keyword = [$keywords => ['card_no', 'mobile', 'ch_name', 'category']];
            if (!$list = $this->search($keyword, $where, $column, $page, $page_num, $order, $asc)) {
                 return false;
            }
        }else{
            if (!$list = $this->getList($where, $column, $order, $asc, $page, $page_num)) {
                return false;
            }
        }
        $list['data'] = ImagesService::getListImagesConcise($list['data'],['avatar_id' => 'single']);
        $member_ids  = array_column($list['data'],'id');
        if (empty($member_info_list  = MemberInfoRepository::getList(['member_id' => ['in',$member_ids]],['member_id','is_recommend','is_home_detail','employer']))){
            $member_info_list = [
                'member_id'     => 0,
                'is_recommend'  => 0,
                'is_home_detail'=> 0,
                'employer'      => '',
            ];
        }
        if (empty($member_grade_list = MemberGradeRepository::getList(['user_id' => ['in',$member_ids]],['user_id','grade']))){
            $member_grade_list = [
                'user_id' => 0,
                'grade' => 0,
            ];
        }
        $member_info_arr  = [];
        $member_grade_arr = [];
        foreach ($list['data'] as &$value){
            if ($member_info = $this->searchArray($member_info_list,'member_id',$value['id'])){
                $member_info_arr = reset($member_info);
            }
            if ($member_grade = $this->searchArray($member_grade_list,'user_id',$value['id'])){
                $member_grade_arr = reset($member_grade);
            }
            $value = array_merge($value,$member_info_arr,$member_grade_arr);
            if (empty($list['is_recommend'])) $value['is_recommend'] = '0'; else $value['is_recommend'] == 0 ? 0 : 1;
            if (empty($value['grade'])) $value['grade_name'] = '普通成员'; else $value['grade_name'] = MemberEnum::getGrade($value['grade']) ;
            $value['category_name'] = MemberEnum::getCategory($value['category'],'普通成员');
            $value['sex_name']      = MemberEnum::getSex($value['sex'],'未设置');
            $value['status_name']   = MemberEnum::getStatus($value['status'],'成员');
            $value['hidden_name']   = MemberEnum::getHidden($value['hidden'],'显示');
            $value['img_url']       = $value['avatar_url']; #前端适配字段名
            unset($value['member_id'],$value['user_id'],$value['avatar_url']);
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
            