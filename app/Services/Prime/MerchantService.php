<?php
namespace App\Services\Prime;


use App\Enums\PrimeTypeEnum;
use App\Repositories\CommonImagesRepository;
use App\Repositories\PrimeMerchantInfoRepository;
use App\Repositories\PrimeMerchantRepository;
use App\Repositories\PrimeMerchantViewRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\Common\ImagesService as CommonImagesService;

class MerchantService extends BaseService
{
    use HelpTrait;
    protected $auth;

    /**
     * EmployeeService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('prime_api');
    }

    /**
     * 用户登录，返回用户信息和TOKEN
     * @param $account
     * @param $password
     * @return mixed|string
     */
    public function login($account, $password){
        //兼容账户登录、手机号登录
        $mobile_regex = '/^(1(([35789][0-9])|(47)))\d{8}$/';
        $account_type = 'account';
        if (preg_match($mobile_regex, $account)) {
            $account_type = 'mobile';
        }
        if (!PrimeMerchantRepository::exists([$account_type => $account])){
            return '用户不存在！';
        }
        $token = PrimeMerchantRepository::login([$account_type => $account, 'password' => $password]);
        if (is_array($token)){
            return $token['message'];
        }
        $user = $this->auth->user();
        $user_info = $user->toArray();
        $user_info['logo'] = CommonImagesRepository::getField(['id' => $user_info['logo_id']],'img_url');
        unset($user_info['logo_id'],$user_info['disabled'],$user_info['created_at'],$user_info['updated_at']);
        return ['user' => $user_info, 'token' => $token];
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @param $token
     * @return bool
     */
    public function logout($token)
    {
        if (PrimeMerchantRepository::logout($token)){
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
        if ($token = PrimeMerchantRepository::refresh($token)){
            return $token;
        }
        return false;
    }

    /**
     * Get user info.
     * @return mixed
     */
    public function getUserInfo(){
        if ($user = PrimeMerchantRepository::getUser()){
            return $user;
        }
        return false;
    }

    /**
     * 添加商户
     * @param $request
     * @return bool
     */
    public function addMerchant($request)
    {
        if (PrimeMerchantRepository::exists(['account' => $request['account']])){
            $this->setError('账户名已被使用');
            return false;
        }
        if (PrimeMerchantRepository::exists(['mobile' => $request['mobile']])){
            $this->setError('手机号已被使用');
            return false;
        }
        if (PrimeMerchantRepository::exists(['name' => $request['name']])){
            $this->setError('该商户名已被使用');
            return false;
        }
        $display_img_count = count(explode(',',$request['display_img_ids']));
        if (($display_img_count % 3) > 0){
            $this->setError('展示图数量必须以3的倍数上传，最少3张');
            return false;
        }
        if ($display_img_count > 18){
            $this->setError('展示图数量最多18张');
            return false;
        }
        $merchant_add_arr = [
            'name'              => $request['name'],
            'account'           => $request['account'],
            'mobile'            => $request['mobile'],
            'realname'          => $request['realname'],
            'logo_id'           => $request['logo_id'],
        ];
        $info_add_arr = [
            'type'              => $request['type'],
            'license'           => $request['license'] ?? '',
            'license_img_id'    => $request['license_img_id'] ?? 0,
            'area_code'         => $request['area_code'] ?? '',
            'longitude'         => $request['log'],
            'latitude'          => $request['lat'],
            'address'           => $request['address'] ?? '',
            'banner_ids'        => $request['banner_ids'],
            'display_img_ids'   => $request['display_img_ids'],
            'shorttitle'        => $request['shorttitle'],
            'describe'          => $request['describe'],
            'star'              => $request['star'] ?? 0,
            'expect_spend'      => $request['expect_spend'] ?? 0,
            'discount'          => $request['discount'] ?? '',
        ];
        if (PrimeMerchantRepository::exists($merchant_add_arr)){
            $this->setError('该商户已添加');
            return false;
        }
        if (PrimeMerchantInfoRepository::exists($info_add_arr)){
            $this->setError('该商户已添加');
            return false;
        }
        $merchant_add_arr['password']    = Hash::make($request['password']);
        $merchant_add_arr['created_at']  = time();
        $merchant_add_arr['updated_at']  = time();
        $info_add_arr['created_at']  = time();
        $info_add_arr['updated_at']  = time();
        DB::beginTransaction();
        if ($merchant_id = PrimeMerchantRepository::getAddId($merchant_add_arr)){
            $info_add_arr['merchant_id'] = $merchant_id;
            if (PrimeMerchantInfoRepository::getAddId($info_add_arr)){
                DB::commit();
                $this->setMessage('添加成功！');
                return true;
            }
        }
        DB::rollBack();
        $this->setError('添加失败！');
        return true;
    }

    /**
     * 开启或禁用商户
     * @param $merchant_id
     * @return bool
     */
    public function disabledMerchant($merchant_id)
    {
        if (!$merchant = PrimeMerchantRepository::getOne(['id' => $merchant_id])){
            $this->setError('商户信息不存在！');
            return false;
        }
        $upd_arr = ['updated_at' => time(),'disabled' => 0];
        $message = '开启';
        if ($merchant['disabled'] == 0){
            $upd_arr['disabled'] = time();
            $message = '禁用';
        }
        if (PrimeMerchantRepository::getUpdId(['id' => $merchant_id],$upd_arr)){
            $this->setMessage($message.'成功！');
            return true;
        }
        $this->setError($message.'失败！');
        return true;
    }

    /**
     * OA修改商户信息
     * @param $request
     * @return bool
     */
    public function editMerchant($request)
    {
        if (!PrimeMerchantRepository::exists(['id' => $request['id']])){
            $this->setError('该商户不存在');
            return false;
        }
        if (PrimeMerchantRepository::exists(['mobile' => $request['mobile'],'id' => ['<>',$request['id']]])){
            $this->setError('手机号已被使用');
            return false;
        }
        if (PrimeMerchantRepository::exists(['name' => $request['name'],'id' => ['<>',$request['id']]])){
            $this->setError('该商户名已被使用');
            return false;
        }
        $display_img_count = count(explode(',',$request['display_img_ids']));
        if (($display_img_count % 3) > 0){
            $this->setError('展示图数量必须以3的倍数上传，最少3张');
            return false;
        }
        if ($display_img_count > 18){
            $this->setError('展示图数量最多18张');
            return false;
        }
        $upd_arr = [
            'name'              => $request['name'],
            'mobile'            => $request['mobile'],
            'realname'          => $request['realname'],
            'logo_id'           => $request['logo_id'],
        ];
        $info_upd_arr = [
            'type'              => $request['type'],
            'license'           => $request['license'] ?? '',
            'license_img_id'    => $request['license_img_id'] ?? 0,
            'area_code'         => $request['area_code'] ?? '',
            'address'           => $request['address'] ?? '',
            'banner_ids'        => $request['banner_ids'],
            'display_img_ids'   => $request['display_img_ids'],
            'shorttitle'        => $request['shorttitle'],
            'describe'          => $request['describe'],
            'star'              => $request['star'] ?? 0,
            'expect_spend'      => $request['expect_spend'] ?? 0,
            'discount'          => $request['discount'] ?? '',
        ];
        if (PrimeMerchantRepository::exists(array_merge($upd_arr,['id' => ['<>',$request['id']]]))){
            $this->setError('该商户已添加');
            return false;
        }
        if (PrimeMerchantInfoRepository::exists(array_merge($info_upd_arr,['merchant_id' => ['<>',$request['id']]]))){
            $this->setError('该商户已添加');
            return false;
        }
        $upd_arr['updated_at']      = time();
        $info_upd_arr['updated_at'] = time();
        DB::beginTransaction();
        if (PrimeMerchantRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            if (PrimeMerchantInfoRepository::getUpdId(['merchant_id' => $request['id']],$info_upd_arr)){
                DB::commit();
                $this->setMessage('修改成功！');
                return true;
            }
        }
        DB::rollBack();
        $this->setError('修改失败！');
        return true;
    }

    /**
     * 商户修改自己信息
     * @param $request
     * @return bool
     */
    public function userEditMerchant($request)
    {
        $merchant = Auth::guard('prime_api')->user();
        if (PrimeMerchantRepository::exists(['mobile' => $request['mobile'],'id' => ['<>',$merchant->id]])){
            $this->setError('手机号已被使用');
            return false;
        }
        if (PrimeMerchantRepository::exists(['name' => $request['name'],'id' => ['<>',$merchant->id]])){
            $this->setError('名称已被使用');
            return false;
        }
        $display_img_count = count(explode(',',$request['display_img_ids']));
        if (($display_img_count % 3) > 0){
            $this->setError('展示图数量必须以3的倍数上传，最少3张');
            return false;
        }
        $upd_arr = [
            'name'              => $request['name'],
            'mobile'            => $request['mobile'],
            'realname'          => $request['realname'],
            'logo_id'           => $request['logo_id'],
        ];
        $info_upd_arr = [
            'address'           => $request['address'] ?? '',
            'banner_ids'        => $request['banner_ids'],
            'display_img_ids'   => $request['display_img_ids'],
            'shorttitle'        => $request['shorttitle'],
            'describe'          => $request['describe'],
            'expect_spend'      => $request['expect_spend'] ?? '',
            'discount'          => $request['discount'] ?? '',
        ];
        if (PrimeMerchantRepository::exists(array_merge($upd_arr,['id' => ['<>',$merchant->id]]))){
            $this->setError('该商户已添加');
            return false;
        }
        if (PrimeMerchantInfoRepository::exists(array_merge($info_upd_arr,['merchant_id' => ['<>',$merchant->id]]))){
            $this->setError('该商户已添加');
            return false;
        }
        $upd_arr['updated_at']      = time();
        $info_upd_arr['updated_at'] = time();
        DB::beginTransaction();
        if (PrimeMerchantRepository::getUpdId(['id' => $merchant->id],$upd_arr)){
            if (PrimeMerchantInfoRepository::getUpdId(['merchant_id' => $merchant->id],$info_upd_arr)){
                DB::commit();
                $this->setMessage('修改成功！');
                return true;
            }
        }
        DB::rollBack();
        $this->setError('修改失败！');
        return true;
    }

    /**
     * 获取商户列表
     * @param $request
     * @return bool|mixed|null
     */
    public function merchantList($request){
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $type       = $request['type'] ?? null;
        $area_code  = $request['area_code'] ?? null;
        $disabled   = $request['disabled'] ?? null;
        $keywords   = $request['keywords'] ?? null;
        $order      = 'id';
        $desc_asc   = 'desc';
        $where      = ['id' => ['>',0]];
        $column     = ['*'];
        if (!empty($type)){
            $where['type'] = $type;
        }
        if (!empty($area_code)){
            $where['area_code'] = ['like','%'.$area_code.',%'];
        }
        if (!empty($disabled)){
            $where['disabled'] = ($disabled == 1) ? 0 : ['>',0];
        }
        if (!empty($keywords)){
            $keywords = [$keywords => ['name','mobile','realname','license']];
            if (!$list = PrimeMerchantViewRepository::search($keywords,$where,$column,$page,$page_num,$order,$desc_asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = PrimeMerchantViewRepository::getList($where,$column,$order,$desc_asc,$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data'] = CommonImagesService::getListImages($list['data'],
            [
                'logo_id'           => 'single',
                'license_img_id'    => 'single',
                'banner_ids'        => 'several',
                'display_img_ids'   => 'several'
            ]
        );
        foreach ($list['data'] as &$value){
            list($area_address)          = $this->makeAddress($value['area_code'],$value['address']);
            $value['area_address']       = $area_address;
            $value['type_title']         = PrimeTypeEnum::getType($value['type']);
            $value['expect_spend_title'] = empty($value['expect_spend']) ? '' : round($value['expect_spend'] / 100,2).'元';
            $value['is_recommend']       = $value['is_recommend'] == 0 ? 2 : 1;
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 推荐或取消推荐
     * @param $merchant_id
     * @param $is_recommend
     * @return bool
     */
    public function isRecommend($merchant_id, $is_recommend)
    {
        if (!$merchant = PrimeMerchantRepository::getOne(['id' => $merchant_id])){
            $this->setError('商户不存在！');
            return false;
        }
        if ($merchant['disabled'] > 0){
            $this->setError('该商户已被禁用！');
            return false;
        }
        if (!$merchant_info = PrimeMerchantInfoRepository::getOne(['merchant_id' => $merchant_id])){
            $this->setError('商户信息不存在！');
            return false;
        }
        $upd_arr = [
            'is_recommend'  => $is_recommend == 1 ? time() : 0,
            'updated_at'    => time()
        ];
        if (!PrimeMerchantInfoRepository::getUpdId(['merchant_id' => $merchant_id],$upd_arr)){
            $this->setError('操作失败！');
            return false;
        }
        $this->setMessage('操作成功！');
        return true;
    }

    /**
     * 首页列表
     * @param $request
     * @return bool|mixed|null
     */
    protected function getHomeList($request){
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $type       = $request['type'] ?? null;
        $keywords   = $request['keywords'] ?? null;
        $order      = 'id';
        $desc_asc   = 'desc';
        $where      = ['disabled' => 0];
        $column     = ['id','name','banner_ids','address','star','expect_spend','discount'];
        if (!empty($type)){
            $where['type'] = $type;
        }
        if (!empty($keywords)){
            $keywords = [$keywords => ['name','mobile','realname','license']];
            if (!$list = PrimeMerchantViewRepository::search($keywords,$where,$column,$page,$page_num,$order,$desc_asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = PrimeMerchantViewRepository::getList($where,$column,$order,$desc_asc,$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data'] = CommonImagesService::getListImages($list['data'], ['banner_ids'=> 'single']
        );
        foreach ($list['data'] as &$value){
            $value['expect_spend_title'] = empty($value['expect_spend']) ? '' : '人均 '.round($value['expect_spend'] / 100,2).' 元';
            unset($value['banner_ids'],$value['logo_id']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            