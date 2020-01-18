<?php


namespace App\Repositories;


use App\Enums\CollectTypeEnum;
use App\Enums\HouseEnum;
use App\Models\HouseDetailsModel;
use App\Repositories\Traits\RepositoryTrait;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;

class HouseDetailsRepository extends ApiRepository
{
    use RepositoryTrait;
    use HelpTrait;

    /**
     * AdminUserRepository constructor.
     * @param $model
     */
    public function __construct(HouseDetailsModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取房屋收藏
     * @param $request
     * @return bool|mixed|null
     */
    protected function getCollectList($request)
    {
        $where      = ['deleted_at' => 0,'status' => HouseEnum::PASS,'id' => ['in',$request['collect_ids']]];
        $order      = 'id';
        $desc_asc   = 'desc';
        $column = ['id','title','area','area_code','describe','rent','tenancy','decoration','image_ids','storey','condo_name','category'];
        if (!$list = $this->getList($where,$column,$order,$desc_asc)){
            return [];
        }
        $list = $this->removePagingField($list);
        if (empty($list)){
            return $list;
        }
        $list['data'] = ImagesService::getListImages($list['data'], ['image_ids' => 'single']);
        foreach ($list['data']  as &$value){
            $value['type'] = $request['type'];
            $value['type_name'] = CollectTypeEnum::getType($request['type'],'');
            #处理地址
            list($area_address) = $this->makeAddress($value['area_code'],'',3);
            $value['area_address']  = $area_address;
            $value['storey']        = $value['storey'].'层';
            #处理价格
            $value['rent_tenancy']  = '¥'. $value['rent'] .'/'. HouseEnum::getTenancy($value['tenancy']);
            $value['decoration'] = HouseEnum::getDecoration($value['decoration']);
            $value['category']      = HouseEnum::getCategory($value['category']);
            unset($value['rent'],$value['image_ids'],$value['area_code'],$value['tenancy']);
        }
        return $list;
    }
}
            