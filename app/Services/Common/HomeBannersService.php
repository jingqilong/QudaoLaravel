<?php
namespace App\Services\Common;


use App\Repositories\CommonHomeBannersRepository;
use App\Repositories\CommonImagesRepository;
use App\Services\BaseService;

class HomeBannersService extends BaseService
{

    /**
     * 获取首页banner
     * @return array|null
     */
    public static function getHomeBanners(){
        $column = ['id','type','related_id','image_id','url'];
        if (CommonHomeBannersRepository::getList(['show_time' => strtotime("today")],$column)){
            $banners = CommonHomeBannersRepository::getList(['show_time' => strtotime("today")]);
        }else{
            if (!$recently_banner = CommonHomeBannersRepository::getOrderOne(['show_time' => ['<',strtotime("today")]],'show_time','desc')){
                return [];
            }
            $banners = CommonHomeBannersRepository::getList(['show_time' => $recently_banner['show_time']],$column);
        }
        foreach ($banners as &$banner){
            $banner['image'] = CommonImagesRepository::getField(['id' => $banner['image_id']],'img_url');
        }
        return $banners;
    }
}
            