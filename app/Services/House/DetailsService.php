<?php
namespace App\Services\House;


use App\Enums\HouseEnum;
use App\Repositories\CommonAreaRepository;
use App\Repositories\CommonImagesRepository;
use App\Repositories\HouseDetailsRepository;
use App\Repositories\HouseDetailsViewRepository;
use App\Repositories\HouseFacilitiesRepository;
use App\Repositories\HouseLeasingRepository;
use App\Repositories\HouseTowardRepository;
use App\Repositories\HouseUnitRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;

class DetailsService extends BaseService
{
    use HelpTrait;

    /**
     * 发布房产信息
     * @param $request
     * @param int $publisher        发布方，默认1个人，2平台
     * @param int $publisher_id     发布人ID，发布方为个人时为会员id，发布方为平台时为oa员工id
     * @return bool
     */
    public function publishHouse($request,int $publisher,int $publisher_id)
    {
        $area_codes = explode(',',$request['area_code']);
        if (count($area_codes) != CommonAreaRepository::count(['code' => ['in',$area_codes]])){
            $this->setError('无效的地区代码！');
            return false;
        }
        if (!HouseLeasingRepository::exists(['id' => $request['leasing_id']])){
            $this->setError('租赁方式不存在！');
            return false;
        }
        if (!HouseUnitRepository::exists(['id' => $request['unit_id']])){
            $this->setError('户型不存在！');
            return false;
        }
        if (!HouseTowardRepository::exists(['id' => $request['toward_id']])){
            $this->setError('朝向不存在！');
            return false;
        }
        if (isset($request['facilities_ids']) && !empty($request['facilities_ids'])){
            $facilities_ids = explode(',',$request['facilities_ids']);
            if (count($facilities_ids) != HouseFacilitiesRepository::count(['id' => ['in',$facilities_ids]])){
                $this->setError('无效的设施！');
                return false;
            }
        }
        $add_arr = [
            'title'         => $request['title'],
            'area_code'     => $request['area_code'] . ',',
            'address'       => $request['address'],
            'describe'      => $request['describe'] ?? '',
            'rent'          => $request['rent'],
            'tenancy'       => $request['tenancy'],
            'leasing_id'    => $request['leasing_id'],
            'decoration'    => $request['decoration'],
            'height'        => $request['height'],
            'area'          => $request['area'],
            'image_ids'     => $request['image_ids'],
            'storey'        => $request['storey'],
            'unit_id'       => $request['unit_id'],
            'condo_name'    => $request['condo_name'] ?? '',
            'toward_id'     => $request['toward_id'],
            'category'      => $request['category'],
            'publisher'     => $publisher,
            'publisher_id'  => $publisher_id,
            'facilities_ids'=> $request['facilities_ids'],
            'status' => 1,
        ];
        if (HouseDetailsRepository::exists($add_arr)){
            $this->setError('房产信息已添加！');
            return false;
        }
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();
        if (!HouseDetailsRepository::getAddId($add_arr)){
            $this->setError('发布失败！');
            return false;
        }
        $this->setMessage('发布成功！');
        return true;
    }

    /**
     * 获取房产详情(前端获取房产详情)
     * @param $id
     * @return bool|null
     */
    public function getHouseDetail($id)
    {
        if (!HouseDetailsRepository::exists(['id' => $id,'deleted_at' => 0])){
            $this->setError('房产信息不存在！');
            return false;
        }
        $column = ['id','title','area_code','address','describe','rent','tenancy','leasing_title','decoration','height','area'
            ,'image_ids','storey','unit_title','condo_name','toward_title','category','publisher','facilities_ids'];
        if (!$house = HouseDetailsViewRepository::getOne(['id' => $id],$column)){
            $this->setError('获取失败！');
            return false;
        }
        #处理地址
        list($area_address,$lng,$lat) = $this->makeAddress($house['area_code'],$house['address']);
        $house['area_address']  = $area_address;
        $house['lng']           = $lng;
        $house['lat']           = $lat;
        #处理价格
        $house['rent']          = '¥'. $house['rent'] .'/'. HouseEnum::getTenancy($house['tenancy']);

        $house['decoration'] = HouseEnum::getDecoration($house['decoration']);
        $house['height']        = $house['height'] .'m';
        $house['area']          = $house['area'] .'㎡';
        $image_list = CommonImagesRepository::getList(['id' => ['in',explode(',',$house['image_ids'])]]);
        $house['images']        = array_column($image_list,'img_url');
        $house['storey']        = $house['storey'] .'层';
        $house['category']      = HouseEnum::getCategory($house['category']);
        $house['publisher']     = HouseEnum::getPublisher($house['publisher']);
        $house['facilities']    = HouseFacilitiesRepository::getFacilitiesList(explode(',',$house['facilities_ids']));
        unset($house['area_code'],$house['tenancy'],$house['image_ids'],
            $house['facilities_ids'],$house['recommend']);
        $this->setMessage('获取成功！');
        return $house;
    }

    /**
     * 加工房产地址，将地区码转换成详细地址，并获取经纬度
     * @param $codes
     * @param $append
     * @return mixed
     */
    protected function  makeAddress($codes, $append){
        $codes      = trim($codes,',');
        $area_codes = explode(',',$codes);
        $area_list  = CommonAreaRepository::getList(['code' => ['in',$area_codes]],['code','name','lng','lat']);
        $area_address = '';
        foreach ($area_codes as $code){
            if ($area = $this->searchArray($area_list,'code',$code)){
                $area_address .= reset($area)['name'];
            }
        }
        $area_address .= $append;
        $lng = '';
        $lat = '';
        if ($area_l_l = $this->searchArray($area_list,'code',end($area_codes))){
            $lng = reset($area_l_l)['lng'];
            $lat = reset($area_l_l)['lat'];
        }
        return [$area_address,$lng,$lat];
    }
}
            