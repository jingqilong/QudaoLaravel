<?php
namespace App\Services\Common;


use App\Enums\CommonHomeEnum;
use App\Repositories\CommonHomeBannersRepository;
use App\Repositories\CommonImagesRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class HomeBannersService extends BaseService
{
    use HelpTrait;

    /**
     * 获取首页banner
     * @param int $module banner模块
     * @param int $count    数量
     * @return array|null
     */
    public static function getHomeBanners($module = CommonHomeEnum::MAINHOME,$count = 4){
        $column = ['id','type','related_id','image_id','url'];
        $where = ['module' => $module];
        if (!$banners = CommonHomeBannersRepository::getList($where,$column,'updated_at','desc',1,$count)){
            return [];
        }
        $banners = $banners['data'];
//        if (CommonHomeBannersRepository::exists($where)){
//            $banners = CommonHomeBannersRepository::getList($where,$column);
//        }else{
//            if (!$recently_banner = CommonHomeBannersRepository::getOrderOne(['show_time' => ['<',strtotime("today")],'module' => $module],'show_time','desc')){
//                return [];
//            }
//            $banners = CommonHomeBannersRepository::getList(['show_time' => $recently_banner['show_time'],'module' => $module],$column);
//        }dd($banners);
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
        $add_arr = [
            'module'        => $request['module'],
            'type'          => $request['type'],
            'show_time'     => strtotime($request['show_time']),
            'related_id'    => $request['related_id'] ?? 0,
            'image_id'      => $request['image_id'],
            'url'           => $request['url'] ?? ''
        ];
        if (CommonHomeBannersRepository::exists($add_arr)){
            $this->setError('该banner已添加！');
            return false;
        }
        $add_arr['created_at'] = $add_arr['updated_at'] = time();
        if (!CommonHomeBannersRepository::getAddId($add_arr)){
            $this->setError('添加失败！');
            return false;
        }
        $this->setMessage('添加成功！');
        return true;
    }

    /**
     * 删除首页banner展示
     * @param $id
     * @return bool
     */
    public function deleteBanner($id){
        if (!$banner = CommonHomeBannersRepository::getOne(['id' => $id])){
            $this->setError('banner记录不存在！');
            return false;
        }
        if (CommonHomeBannersRepository::count(['module' => $banner['module']]) == 1){
            $this->setError('该模块仅剩一张banner图，无法删除！');
            return false;
        }
        if (!CommonHomeBannersRepository::delete(['id' => $id])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 编辑首页展示banner
     * @param $request
     * @return bool
     */
    public function editBanner($request){
        if (!$banner = CommonHomeBannersRepository::getOne(['id' => $request['id']])){
            $this->setError('banner记录不存在！');
            return false;
        }
        $upd_arr = [
            'module'        => $request['module'],
            'type'          => $request['type'],
            'show_time'     => strtotime($request['show_time']),
            'related_id'    => $request['related_id'] ?? 0,
            'image_id'      => $request['image_id'],
            'url'           => $request['url'] ?? ''
        ];
        if (CommonHomeBannersRepository::exists(array_merge($upd_arr,['id' => ['<>',$request['id']]]))){
            $this->setError('该banner已存在！');
            return false;
        }
        $upd_arr['updated_at'] = time();
        if (!CommonHomeBannersRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('修改失败！');
            return false;
        }
        $this->setMessage('修改成功！');
        return true;
    }

    /**
     * 获取banner列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getBannerList($request){
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $module     = $request['module'] ?? null;
        $type       = $request['type'] ?? null;
        $where      = ['id' => ['>',0]];
        if (!empty($module)){
            $where['module'] = $module;
        }
        if (!empty($type)){
            $where['type'] = $type;
        }
        if (!$list = CommonHomeBannersRepository::getList($where,['*'],'id','desc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data'] = ImagesService::getListImages($list['data'],['image_id' => 'single']);
        foreach ($list['data'] as &$datum){
            $datum['module_title']  = CommonHomeEnum::getBannerModule($datum['module']);
            $datum['type_title']    = CommonHomeEnum::getBannerType($datum['type']);
            $datum['show_time']     = empty($datum['show_time']) ? '' : date('Y-m-d',$datum['show_time']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            