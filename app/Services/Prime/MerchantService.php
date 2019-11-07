<?php
namespace App\Services\Prime;


use App\Enums\PrimeTypeEnum;
use App\Repositories\CommonImagesRepository;
use App\Repositories\PrimeMerchantRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
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
     * @param $mobile
     * @param $password
     * @return mixed|string
     */
    public function login($mobile, $password){

        if (!PrimeMerchantRepository::exists(['mobile' => $mobile])){
            return '用户不存在！';
        }
        $token = PrimeMerchantRepository::login($mobile,$password);
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
        if (PrimeMerchantRepository::exists(['mobile' => $request['mobile']])){
            $this->setError('手机号已被使用');
            return false;
        }
        if (PrimeMerchantRepository::exists(['name' => $request['name']])){
            $this->setError('该商户名已被使用');
            return false;
        }
        $add_arr = [
            'name'          => $request['name'],
            'mobile'        => $request['mobile'],
            'realname'      => $request['realname'],
            'logo_id'       => $request['logo_id'],
            'type'          => $request['type'],
            'license'       => $request['license'] ?? '',
            'license_img_id' => $request['license_img_id'] ?? '',
            'area_code'     => $request['area_code'] ?? '',
            'address'       => $request['address'] ?? '',
            'banner_ids'    => $request['banner_ids'],
            'display_img_ids' => $request['display_img_ids'],
            'describe'      => $request['describe'],
            'expect_spend'  => $request['expect_spend'] ?? '',
            'discount'      => $request['discount'] ?? '',
        ];
        if (PrimeMerchantRepository::exists($add_arr)){
            $this->setError('该商户已添加');
            return false;
        }
        $add_arr['password']    = Hash::make($request['password']);
        $add_arr['created_at']  = time();
        $add_arr['updated_at']  = time();
        if (PrimeMerchantRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
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
     * 修改商户信息
     * @param $request
     * @return bool
     */
    public function editMerchant($request)
    {
        if (PrimeMerchantRepository::exists(['id' => $request['id']])){
            $this->setError('该商户不存在');
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
        $upd_arr = [
            'name'          => $request['name'],
            'mobile'        => $request['mobile'],
            'realname'      => $request['realname'],
            'logo_id'       => $request['logo_id'],
            'type'          => $request['type'],
            'license'       => $request['license'] ?? '',
            'license_img_id' => $request['license_img_id'] ?? '',
            'area_code'     => $request['area_code'] ?? '',
            'address'       => $request['address'] ?? '',
            'banner_ids'    => $request['banner_ids'],
            'display_img_ids' => $request['display_img_ids'],
            'describe'      => $request['describe'],
            'expect_spend'  => $request['expect_spend'] ?? '',
            'discount'      => $request['discount'] ?? '',
        ];
        if (PrimeMerchantRepository::exists(array_merge($upd_arr,['id' => ['<>',$request['id']]]))){
            $this->setError('该商户已添加');
            return false;
        }
        $upd_arr['updated_at']  = time();
        if (PrimeMerchantRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
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
            if (!$list = PrimeMerchantRepository::search($keywords,$where,$column,$page,$page_num,$order,$desc_asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = PrimeMerchantRepository::getList($where,$column,$order,$desc_asc,$page,$page_num)){
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
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            