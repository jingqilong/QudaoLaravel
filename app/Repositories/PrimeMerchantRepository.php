<?php


namespace App\Repositories;


use App\Enums\CollectTypeEnum;
use App\Enums\PrimeTypeEnum;
use App\Enums\ProcessActionEnum;
use App\Models\PrimeMerchantModel;
use App\Repositories\Traits\RepositoryTrait;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PrimeMerchantRepository extends ApiRepository
{
    use RepositoryTrait,HelpTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(PrimeMerchantModel $model)
    {
        $this->model = $model;
    }
    /**
     * Get a JWT via given credentials.
     *
     * @param array $account_password       包含账户和密码，账户、手机号
     * @return array|JsonResponse|string
     */
    protected function login (array $account_password = ['account' => '','password' => '']){
        $where = $account_password;
        unset($where['password']);
        if (!$user = $this->model->where($where)->first()){
            return ['code' => 100, 'message' => '账户不存在'];
        }
        if (!Hash::check($account_password['password'], $user->password)){
            return ['code' => 100, 'message' => '密码不正确'];
        }
        if ($user->disabled != 0){
            return ['code' => 100, 'message' => '您的账户已被禁用，如有疑问，请致电客服！'];
        }
        $auth = Auth::guard('prime_api');
        if (! $token = $auth->fromUser($user)) {
            return ['code' => 100, 'message' => '账户或密码不正确'];
        }
        $auth->setToken($token);
        return $token;
    }


    /**
     * Get the authenticated User.
     *
     * @return mixed
     */
    protected function getUser()
    {
        $auth       = Auth::guard('prime_api');
        $user       = $auth->user();
        $column     = ['id','name','account','mobile','realname','logo_id','type','license','license_img_id','area_code','banner_ids','display_img_ids','address','shorttitle','describe','expect_spend','discount'];
        $user_info  = PrimeMerchantViewRepository::getOne(['id' => $user->id],$column);
        list($area_address)         = $this->makeAddress($user_info['area_code'],$user_info['address']);
        $user_info['area_address']  = $area_address;
        $banner_ids         = explode(',',$user_info['banner_ids']);
        $display_img_ids    = explode(',',$user_info['display_img_ids']);
        $image_ids          = array_merge([$user_info['logo_id'],$user_info['license_img_id']],$banner_ids,$display_img_ids);
        $image_list         = CommonImagesRepository::getList(['id' => ['in',$image_ids]],['id','img_url']);
        $user_info['logo']  = '';
        if ($logo = $this->searchArray($image_list,'id',$user_info['logo_id'])){
            $user_info['logo'] = reset($logo)['img_url'];
        }
        $user_info['license_img'] = '';
        if ($logo = $this->searchArray($image_list,'id',$user_info['license_img_id'])){
            $user_info['license_img'] = reset($logo)['img_url'];
        }
        $user_info['banners'] = [];
        foreach ($banner_ids as $banner_id){
            if ($banner = $this->searchArray($image_list,'id',$banner_id)){
                $user_info['banners'][] = $banner;
            }
        }
        $user_info['display_imgs'] = [];
        foreach ($display_img_ids as $display_img_id){
            if ($display_img = $this->searchArray($image_list,'id',$display_img_id)){
                $user_info['display_imgs'][] = $display_img;
            }
        }
        unset($user_info['type'],$user_info['license_img_id'],
            $user_info['area_code']);
        return $user_info;
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return bool
     */
    protected function logout()
    {
        $auth = Auth::guard('prime_api');
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
        $auth = Auth::guard('prime_api');
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
        $auth = Auth::guard('prime_api');
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $auth->factory()->getTTL() * 60
        ];
    }


    /**
     * 获取餐饮收藏列表
     * @param array $request
     * @return array|mixed|null
     */
    protected function getCollectList(array $request)
    {
        $order      = 'id';
        $desc_asc   = 'desc';
        $where      = ['id' => ['in',$request['collect_ids']],'disabled' => 0];
        $column     = ['id','name','type','banner_ids','address','star','expect_spend','discount'];
        if (!$list = PrimeMerchantViewRepository::getList($where,$column,$order,$desc_asc)){
            return [];
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            return $list;
        }
        $list['data'] = ImagesService::getListImages($list['data'], ['banner_ids' => 'single']);
        foreach ($list['data'] as &$value){
            $value['expect_spend_title'] = empty($value['expect_spend']) ? '' : '人均 '.round($value['expect_spend'] / 100,2).' 元';
            $value['type_name']  = PrimeTypeEnum::getType($value['type'],'');
            unset($value['banner_ids'],$value['logo_id']);
        }
        return $list;
    }

}
            