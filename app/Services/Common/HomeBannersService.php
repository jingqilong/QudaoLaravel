<?php
namespace App\Services\Common;


use App\Enums\CommonHomeEnum;
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
        if (CommonHomeBannersRepository::exists(['show_time' => strtotime("today")],$column)){
            $banners = CommonHomeBannersRepository::getList(['show_time' => strtotime("today")],$column);
        }else{
            if (!$recently_banner = CommonHomeBannersRepository::getOrderOne(['show_time' => ['<',strtotime("today")]],'show_time','desc')){
                return [];
            }
            $banners = CommonHomeBannersRepository::getList(['show_time' => $recently_banner['show_time']],$column);
        }
        foreach ($banners as &$banner){
            $banner['image'] = CommonImagesRepository::getField(['id' => $banner['image_id']],'img_url');
            $banner['type_name'] = CommonHomeEnum::getBannerType($banner['type']);
            unset($banner['image_id']);
        }
        return $banners;
    }


    /**
     * 添加首页展示banner
     * @param $request
     * @return bool
     */
    public function addBanners($request){
        $type = $request['type'];
        $add_arr = [
            'type'          => $type,
            'show_time'     => strtotime($request['show_time']),
            'related_id'    => $request['related_id'] ?? 0,
            'image_id'      => $request['image_id'],
            'url'           => $request['url'] ?? '',
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if (CommonHomeBannersRepository::exists($add_arr)){
            $this->setError('该banner已添加！');
            return false;
        }
        if (!CommonHomeBannersRepository::getAddId($add_arr)){
            $this->setError('添加失败！');
            return false;
        }
        $this->setMessage('添加成功！');
        return true;
    }
}
            