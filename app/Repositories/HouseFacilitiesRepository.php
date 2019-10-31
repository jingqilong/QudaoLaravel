<?php


namespace App\Repositories;


use App\Models\HouseFacilitiesModel;
use App\Repositories\Traits\RepositoryTrait;
use App\Traits\HelpTrait;

class HouseFacilitiesRepository extends ApiRepository
{
    use RepositoryTrait,HelpTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(HouseFacilitiesModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取设施列表
     * @param array $ids
     * @return array|null
     */
    protected function getFacilitiesList(array $ids){
        if (!$list = $this->getList(['id' => ['in',$ids]],['title','icon_id'])){
            return [];
        }
        $icon_ids = array_column($list,'icon_id');
        $icon_list = CommonImagesRepository::getList(['id' => ['in',$icon_ids]]);
        foreach ($list as &$value){
            $value['icon'] = '';
            if ($icon = $this->searchArray($icon_list,'id',$value['icon_id'])){
                $value['icon'] = reset($icon)['img_url'];
            }
            unset($value['icon_id']);
        }
        return $list;
    }
}
            