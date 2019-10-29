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
        $column         = ['id','code','name','short_name','lng','lat'];
        if (!$list = CommonAreaRepository::getList(['parent_code' => $parent_code],$column,'sort','asc')){
            $this->setMessage('暂无信息！');
            return [];
        }
        $this->setMessage('获取成功！');
        return $list;
    }
}
            