<?php
namespace App\Services\Prime;


use App\Enums\CollectTypeEnum;
use App\Repositories\CommonImagesRepository;
use App\Repositories\MemberCollectRepository;
use App\Repositories\PrimeMerchantProductsRepository;
use App\Repositories\PrimeMerchantViewRepository;
use App\Services\BaseService;
use App\Services\Common\ImagesService as CommonImagesService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

class MerchantInfoService extends BaseService
{
    use HelpTrait;
    /**
     * 获取首页列表
     * @param $request
     * @return mixed
     */
    public function getHomeList($request)
    {
        $type       = $request['type'] ?? null;
        $res['recommend']       = PrimeMerchantViewRepository::getOneRecommend($type);
        $res['list']            = MerchantService::getHomeList($request);
        $this->setMessage('获取成功！');
        return $res;
    }

    /**
     * 前端获取商户详情
     * @param $merchant_id
     * @return bool|null
     */
    public function getMerchantDetail($merchant_id)
    {
        $member = Auth::guard('member_api')->user();
        $column = ['id','name','area_code','banner_ids','display_img_ids','address','shorttitle','describe','expect_spend','discount'];
        if (!$merchant = PrimeMerchantViewRepository::getOne(['id' => $merchant_id],$column)){
            $this->setError('该商户信息不存在！');
            return false;
        }
        #获取推荐产品
        $merchant['products'] = [];
        $product_column = ['id','title','describe','image_ids'];
        if ($products = PrimeMerchantProductsRepository::getList(['merchant_id' => $merchant_id,'is_recommend' => ['>',0]],$product_column)){
            $products = CommonImagesService::getListImages($products,['image_ids' => 'single']);
            $merchant['products'] = $products;
        }
        #获取banner图
        $merchant['banners'] = [];
        if ($banner_ids = explode(',',$merchant['banner_ids'])){
            $banner_list = CommonImagesRepository::getAssignList($banner_ids);
            $merchant['banner_url'] = array_column($banner_list,'img_url');
        }
        #获取展示图
        $merchant['displays'] = [];
        if ($display_img_ids = explode(',',$merchant['display_img_ids'])){
            $display_img_list = CommonImagesRepository::getAssignList($display_img_ids);
            $merchant['displays'] = array_column($display_img_list,'img_url');
        }
        #最低消费
        $merchant['expect_spend'] = empty($merchant['expect_spend']) ? '' : round($merchant['expect_spend'] / 100,2).'元';
        #详细地址
        list($area_address) = $this->makeAddress($merchant['area_code'],$merchant['address']);
        $merchant['area_address'] = $area_address;
        #是否收藏
        $collect_where = ['type' => CollectTypeEnum::PRIME,'target_id' => $merchant['id'],'member_id' => $member->m_id,'deleted_at' => 0];
        $merchant['is_collect'] = MemberCollectRepository::exists($collect_where) ? 1 : 0;
        unset($merchant['area_code'],$merchant['address'],$merchant['banner_ids'],$merchant['display_img_ids'],$merchant['address']);
        $this->setMessage('获取成功！');
        return $merchant;
    }
}
            