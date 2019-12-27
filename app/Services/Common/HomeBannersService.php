<?php
namespace App\Services\Common;


use App\Enums\CommonHomeEnum;
use App\Repositories\ActivityDetailRepository;
use App\Repositories\CommonHomeBannersRepository;
use App\Repositories\CommonImagesRepository;
use App\Repositories\HouseDetailsRepository;
use App\Repositories\MemberBaseRepository;
use App\Repositories\OaEmployeeRepository;
use App\Repositories\PrimeMerchantRepository;
use App\Repositories\ShopGoodsRepository;
use App\Services\BaseService;
use App\Services\Oa\EmployeeService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Tolawho\Loggy\Facades\Loggy;

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
        $column = ['id','link_type','related_id','image_id','url'];
        $where = ['page_space' => $module,'status' => CommonHomeEnum::SHOW];
        if (!$banners = CommonHomeBannersRepository::getList($where,$column,'sort','asc')){
            return [];
        }
        $banners = ImagesService::getListImagesConcise($banners,['image_id' => 'single'],true);
        $activity_list = [];
        if ($activity_banner_list = self::searchArrays($banners,'link_type',CommonHomeEnum::ACTIVITY)){
            $activity_ids  = array_column($activity_banner_list,'related_id');
            $activity_list = ActivityDetailRepository::getAssignList($activity_ids,['id','start_time','end_time']);
        }
        foreach ($banners as &$banner){
            $banner['status']       = 0;
            $banner['status_title'] = '无状态';
            $banner['image']        = $banner['image_url'];
            $banner['type']         = $banner['link_type'];#兼容前端
            if ($banner['link_type'] == CommonHomeEnum::ACTIVITY && !empty($activity_list) &&
                $activity = self::searchArrays($activity_list,'id',$banner['related_id'])
            ){
                $activity = reset($activity);
                if ($activity['start_time'] > time()){
                    $banner['status'] = 1;
                    $banner['status_title'] = '未开始';
                }
                if ($activity['start_time'] < time() && $activity['end_time'] > time()){
                    $banner['status'] = 2;
                    $banner['status_title'] = '进行中';
                }
                if ($activity['end_time'] < time()){
                    $banner['status'] = 3;
                    $banner['status_title'] = '已结束';
                }
            }
        }
        return $banners;
    }


    /**
     * 添加首页展示banner
     * @param $request
     * @return bool
     */
    public function addBanners($request){
        $employee = Auth::guard('oa_api')->user();
        $employee_id = $employee->id;
        $related_id = $request['related_id'] ?? 0;
        if ($request['link_type'] !== CommonHomeEnum::AD && empty($related_id)){
            $this->setError('链接目标不能为空');
            return false;
        }
        $add_arr = [
            'page_space'    => $request['page_space'],
            'link_type'     => $request['link_type'],
            'sort'          => $request['sort'],
            'related_id'    => $related_id,
            'image_id'      => $request['image_id'],
            'url'           => $request['url'] ?? '',
            'status'        => $request['status']
        ];
        //检查是否重复添加
        if (!empty($related_id) && CommonHomeBannersRepository::exists(
            ['page_space' => $request['page_space'], 'link_type' => $request['link_type'],'related_id' => $related_id]
            )){
            $this->setError('该链接目标已添加，请勿重复添加！');
            return false;
        }
        //如果状态为展示，需要检查添加的banner相同展示（顺序）位置是否已有展示信息，如果有，关闭之前的展示信息
        $check_where = ['page_space' => $request['page_space'],'sort' => $request['sort'],'status' => CommonHomeEnum::SHOW];
        if ($request['status'] == CommonHomeEnum::SHOW && $show_banner = CommonHomeBannersRepository::getOne($check_where)){
            $upd_show = ['status' => CommonHomeEnum::HIDDEN,'updated_by' => $employee_id,'updated_at' => time()];
            if (!CommonHomeBannersRepository::getUpdId(['id' => $show_banner['id']],$upd_show)){
                $this->setError('添加失败！');
                Loggy::write('error','后台添加首页banner图出错，错误信息：更新替换banner时出现错误，未能更新成功！');
                return false;
            }
        }
        $add_arr['created_by'] = $employee_id;
        $add_arr['created_at'] = time();
        $add_arr['updated_by'] = $employee_id;
        $add_arr['updated_at'] = time();
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
            $this->setError('当前记录不存在！');
            return false;
        }
        if ($banner['status'] == CommonHomeEnum::SHOW){
            $this->setError('当前信息正在展示，无法删除！');
            return false;
        }
        if (CommonHomeBannersRepository::count(['page_space' => $banner['page_space']]) == 1){
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
        $employee       = Auth::guard('oa_api')->user();
        $employee_id    = $employee->id;
        $related_id     = $request['related_id'] ?? 0;
        if ($request['link_type'] !== CommonHomeEnum::AD && empty($related_id)){
            $this->setError('链接目标不能为空');
            return false;
        }
        if (!$banner = CommonHomeBannersRepository::getOne(['id' => $request['id']])){
            $this->setError('banner记录不存在！');
            return false;
        }
        $upd_arr = [
            'page_space'    => $request['page_space'],
            'link_type'     => $request['link_type'],
            'sort'          => $request['sort'],
            'related_id'    => $related_id,
            'image_id'      => $request['image_id'],
            'url'           => $request['url'] ?? '',
            'status'        => $request['status']
        ];
        //检查是否重复添加
        if (!empty($related_id) && CommonHomeBannersRepository::exists(
                ['page_space' => $request['page_space'], 'link_type' => $request['link_type'],'related_id' => $related_id,'id' => ['<>',$request['id']]]
            )){
            $this->setError('该链接目标已添加，请勿重复添加！');
            return false;
        }
        //如果状态为展示，需要检查编辑的banner相同展示（顺序）位置是否已有展示信息，如果有，关闭之前的展示信息
        $check_where = ['page_space' => $request['page_space'],'sort' => $request['sort'],'status' => CommonHomeEnum::SHOW];
        if ($show_banner = CommonHomeBannersRepository::getOne($check_where)){
            $upd_show = ['status' => CommonHomeEnum::HIDDEN,'updated_by' => $employee_id,'updated_at' => time()];
            if (!CommonHomeBannersRepository::getUpdId(['id' => $show_banner['id']],$upd_show)){
                $this->setError('修改失败！');
                Loggy::write('error','后台修改首页banner图出错，错误信息：更新替换banner时出现错误，未能更新成功！');
                return false;
            }
        }
        $upd_arr['updated_by'] = $employee_id;
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
        $page_space = $request['page_space'] ?? null;
        $link_type  = $request['link_type'] ?? null;
        $status     = $request['status'] ?? null;
        $where      = ['id' => ['>',0]];
        if (!empty($page_space)){
            $where['page_space'] = $page_space;
        }
        if (!empty($link_type)){
            $where['link_type'] = $link_type;
        }
        if (!empty($status)){
            $where['status'] = $status;
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
        $list['data']   = $this->getBannerListRelatedInfo($list['data']);
        $list['data']   = EmployeeService::getListOperationByName($list['data']);
        $list['data']   = ImagesService::getListImages($list['data'],['image_id' => 'single']);
        foreach ($list['data'] as &$datum){
            $datum['page_space_title']  = CommonHomeEnum::getBannerModule($datum['page_space']);
            $datum['link_type_title']   = CommonHomeEnum::getBannerType($datum['link_type']);
            $datum['sort_title']        = CommonHomeEnum::getSort($datum['sort']);
            $datum['status_title']      = CommonHomeEnum::getStatus($datum['status']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 各类别删除数据前的检查
     * @param $type
     * @param $related_id
     * @return bool
     */
    public function deleteBeforeCheck($type, $related_id){
        if ($banner_list = CommonHomeBannersRepository::getList(['type' => $type,'related_id' => $related_id])){
            foreach ($banner_list as $value){
                $this->setError('当前数据正在' . CommonHomeEnum::getBannerModule($value['module']) . '展示，请先取消展示后再删除！');
                return false;
            }
        }
        $this->setMessage('当前数据不在展示列表');
        return true;
    }

    /**
     * banner开关
     * @param $request
     * @return bool
     */
    public function bannerStatusSwitch($request)
    {
        $employee       = Auth::guard('oa_api')->user();
        $employee_id    = $employee->id;
        if (!$banner = CommonHomeBannersRepository::getOne(['id' => $request['id']])){
            $this->setError('banner记录不存在！');
            return false;
        }
        //如果状态为展示，需要检查编辑的banner相同展示（顺序）位置是否已有展示信息，如果有，关闭之前的展示信息
        $check_where = ['page_space' => $banner['page_space'],'status' => CommonHomeEnum::SHOW];
        if (CommonHomeEnum::HIDDEN == $request['status']){
            if (1 == CommonHomeBannersRepository::count($check_where)){
                $this->setError('当前显示位置仅剩一张banner，无法再隐藏！');
                return false;
            }
        }else{
            if ($show_banner = CommonHomeBannersRepository::getOne($check_where)){
                $upd_show = ['status' => CommonHomeEnum::HIDDEN,'updated_by' => $employee_id,'updated_at' => time()];
                if (!CommonHomeBannersRepository::getUpdId(['id' => $show_banner['id']],$upd_show)){
                    $this->setError('操作失败！');
                    Loggy::write('error','后台修改(状态开关)首页banner图出错，错误信息：更新替换banner时出现错误，未能更新成功！');
                    return false;
                }
            }
        }
        $upd_arr['status']      = $request['status'];
        $upd_arr['updated_by']  = $employee_id;
        $upd_arr['updated_at']  = time();
        if (!CommonHomeBannersRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('操作失败！');
            return false;
        }
        $this->setMessage('操作成功！');
        return true;
    }

    /**
     * 获取首页banner列表中相关的信息
     * @param $banner_list
     * @return array
     */
    protected function getBannerListRelatedInfo($banner_list){
        if (empty($banner_list)){
            return [];
        }
        //banner链接类别对应的Repository
        $banner_type_to_repository = [
            CommonHomeEnum::ACTIVITY    => ActivityDetailRepository::class,
            CommonHomeEnum::MEMBER      => MemberBaseRepository::class,
            CommonHomeEnum::SHOP        => ShopGoodsRepository::class,
            CommonHomeEnum::HOUSE       => HouseDetailsRepository::class,
            CommonHomeEnum::PRIME       => PrimeMerchantRepository::class,
        ];
        //banner链接类别对应的相关信息的名字字段
        $banner_type_to_column = [
            CommonHomeEnum::ACTIVITY    =>  'name',
            CommonHomeEnum::MEMBER      =>  'ch_name',
            CommonHomeEnum::SHOP        =>  'name',
            CommonHomeEnum::HOUSE       =>  'title',
            CommonHomeEnum::PRIME       =>  'name',
        ];
        //获取相关的信息
        $related_infos = [];
        foreach ($banner_type_to_repository as $type => $repository){
            if (${$type."banners"} = $this->searchArray($banner_list,'link_type',$type)){
                ${$type."_ids"} = array_column(${$type."banners"},'related_id');
                $related_infos[$type]  = $repository::getlist(['id' => ['in',${$type."_ids"}]]);
            }
        }
        //将相关的信息写入列表
        foreach ($banner_list as &$value){
            $value['related_name'] = '';
            if (in_array($value['link_type'],[CommonHomeEnum::AD])){//广告是没有相关信息的
                continue;
            }
            if ($relate = $this->searchArray($related_infos[$value['link_type']],'id',$value['related_id'])){
                $value['related_name'] = reset($relate)[$banner_type_to_column[$value['link_type']]];
            }
        }
        return $banner_list;
    }
}
            