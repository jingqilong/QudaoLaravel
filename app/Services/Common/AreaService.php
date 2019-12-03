<?php
namespace App\Services\Common;


use App\Repositories\CommonAreaRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class AreaService extends BaseService
{
    use HelpTrait;

    /**
     * 获取省市区街道四级联动列表
     * @param $parent_code
     * @return bool|mixed|null
     */
    public function getAreaList($parent_code)
    {
        $column    = ['id','code','name','memo','image_url','short_name','lng','lat'];
        if (!$list = CommonAreaRepository::getList(['parent_code' => $parent_code],$column,'sort','asc')){
            $this->setMessage('暂无信息！');
            return [];
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 设置省市区地域的图片和备注
     * @param $request
     * @return bool
     */
    public function setAreaImg($request)
    {
        $image_url  = $request['image_url'] ?? '';
        $memo       = $request['memo'] ?? '';
        if (!CommonAreaRepository::exists(['id' => $request['id']])){
            $this->setError('无效ID');
            return false;
        }
        $upd_arr = [
            'image_url'     => $image_url,
            'memo'          => $memo
        ];
        if (!CommonAreaRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('修改失败!');
            return false;
        }
        $this->setMessage('修改成功!');
        return true;
    }
}
            