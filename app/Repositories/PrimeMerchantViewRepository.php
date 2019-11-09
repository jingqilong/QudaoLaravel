<?php


namespace App\Repositories;


use App\Models\PrimeMerchantViewModel;
use App\Repositories\Traits\RepositoryTrait;

class PrimeMerchantViewRepository extends ApiRepository
{
    use RepositoryTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(PrimeMerchantViewModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取推荐商家
     * @param $type
     * @return array|null
     */
    protected function getOneRecommend($type){
        $where = ['id' => ['<>',0]];
        if (!empty($type)){
            $where['type'] = $type;
        }
        $column = ['id','name','banner_ids','display_img_ids','shorttitle','star'];
        if (!$merchant = $this->getOrderOne($where,'is_recommend','desc',$column)){
            return [];
        }
        $merchant['banner_url'] = '';
        if ($banner_ids = explode(',',$merchant['banner_ids'])){
            $banner = CommonImagesRepository::getOne(['id' => reset($banner_ids)]);
            $merchant['banner_url'] = $banner['img_url'];
        }
        $merchant['display_imgs'] = [];
        if ($display_img_ids = explode(',',$merchant['display_img_ids'])){
            $display_img_list = CommonImagesRepository::getAssignList($display_img_ids);
            $merchant['display_imgs'] = array_column($display_img_list,'img_url');
        }
        unset($merchant['banner_ids'],$merchant['display_img_ids']);
        return $merchant;
    }
}
            