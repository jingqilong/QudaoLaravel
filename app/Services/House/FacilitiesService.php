<?php
namespace App\Services\House;


use App\Repositories\CommonImagesRepository;
use App\Repositories\HouseDetailsRepository;
use App\Repositories\HouseFacilitiesRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class FacilitiesService extends BaseService
{
    use HelpTrait;

    /**
     * 添加设施
     * @param $request
     * @return bool
     */
    public function addFacility($request)
    {
        $add_arr = [
            'title'     => $request['title'],
        ];
        if (HouseFacilitiesRepository::exists($add_arr)){
            $this->setError('设施已存在！');
            return false;
        }
        $add_arr['icon_id']    = $request['icon_id'];
        $add_arr['describe']   = $request['describe'] ?? '';
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();
        if (HouseFacilitiesRepository::getAddId($add_arr)){
            $this->setMessage('添加成功！');
            return true;
        }
        $this->setError('添加失败！');
        return false;
    }
    /**
     * 删除设施
     * @param $id
     * @return bool
     */
    public function deleteFacility($id)
    {
        if (!HouseFacilitiesRepository::exists(['id' => $id])){
            $this->setError('设施已删除！');
            return false;
        }
        if (HouseDetailsRepository::exists(['facilities_ids' => ['like','%'.$id.'%']])){
            $this->setError('该设施正在使用，无法删除！');
            return false;
        }
        if (HouseFacilitiesRepository::delete(['id' => $id])){
            $this->setMessage('删除成功！');
            return true;
        }
        $this->setError('删除失败！');
        return false;
    }


    /**
     * 修改设施
     * @param $request
     * @return bool
     */
    public function editFacility($request)
    {
        if (!HouseFacilitiesRepository::exists(['id' => $request['id']])){
            $this->setError('设施信息不存在！');
            return false;
        }
        $upd_arr = [
            'title'     => $request['title'],
        ];
        if (HouseFacilitiesRepository::exists(array_merge($upd_arr,['id' => ['<>',$request['id']]]))){
            $this->setError('设施已存在！');
            return false;
        }
        $upd_arr['icon_id']    = $request['icon_id'];
        $upd_arr['describe']   = $request['describe'] ?? '';
        $upd_arr['updated_at'] = time();
        if (HouseFacilitiesRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setMessage('修改成功！');
            return true;
        }
        $this->setError('修改失败！');
        return false;
    }

    /**
     * 获取设施列表
     * @param $request
     * @return bool
     */
    public function getFacilityList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        if (!$list = HouseFacilitiesRepository::getList(['id' => ['>',0]],['*'],'id','asc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $icon_ids   = array_column($list['data'],'icon_id');
        $icon_list  = CommonImagesRepository::getList(['id' => ['in',$icon_ids]],['id','img_url']);
        foreach ($list['data'] as &$value){
            $value['icon'] = '';
            if ($icon = $this->searchArray($icon_list,'id',$value['icon_id'])){
                $value['icon'] = $icon['img_url'];
            }
            $value['created_at'] = date('Y-m-d H:i:s',$value['created_at']);
            $value['updated_at'] = date('Y-m-d H:i:s',$value['updated_at']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            