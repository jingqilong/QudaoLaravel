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
            'facilities_ids'=> $request['facilities_ids'] . ',',
            'status'        => HouseEnum::PENDING,
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

    /**
     * 软删除房源
     * @param $id
     * @param int $publisher        发布方，默认1个人，2平台
     * @param int $publisher_id     发布人ID，发布方为个人时为会员id，发布方为平台时为oa员工id
     * @return bool
     */
    public function deleteHouse($id, int $publisher, int $publisher_id)
    {
        $where = ['id' => $id,'deleted_at' => 0 ];
        if ($publisher == HouseEnum::PERSON){
            $where['publisher']     = $publisher;
            $where['publisher_id']  = $publisher_id;
        }
        if (!$house = HouseDetailsRepository::getOne($where)){
            $this->setError('该房源不存在！');
            return false;
        }
        if (HouseEnum::PASS == $house['status']){
            $this->setError('该房源正在上架，无法删除！');
            return false;
        }
        if (!HouseDetailsRepository::update($where,['deleted_at' => time()])){
            $this->setError('删除失败！');
            return false;
        }
        $this->setMessage('删除成功！');
        return true;
    }

    /**
     * 修改房源
     * @param $request
     * @param int $publisher
     * @param int $publisher_id
     * @return bool
     */
    public function editHouse($request, int $publisher, int $publisher_id)
    {
        $where = ['id' => $request['id']];
        if ($publisher == HouseEnum::PERSON){
            $where['publisher']     = $publisher;
            $where['publisher_id']  = $publisher_id;
        }
        if (!HouseDetailsRepository::exists($where)){
            $this->setError('房源不存在！');
            return false;
        }
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
        $upd_arr = [
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
            'facilities_ids'=> $request['facilities_ids'] . ',',
            'status'        => HouseEnum::PENDING,
        ];
        if (HouseDetailsRepository::exists(array_merge($upd_arr,['id' => ['<>',$request['id']]]))){
            $this->setError('房产信息已添加！');
            return false;
        }
        $add_arr['updated_at'] = time();
        if (!HouseDetailsRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('修改失败！');
            return false;
        }
        $this->setMessage('修改成功！');
        return true;
    }

    /**
     * 后台获取房源列表
     * @param $request
     * @return bool|mixed|null
     */
    public function houseList($request)
    {
        $keywords   = $request['keywords'] ?? '';
        $decoration = $request['decoration'] ?? '';
        $publisher  = $request['publisher'] ?? '';
        $status     = $request['status'] ?? '';
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $where      = ['deleted_at' => 0];
        if (!empty($decoration)){
            $where['decoration'] = $decoration;
        }
        if (!empty($publisher)){
            $where['publisher'] = $publisher;
        }
        if (!empty($status)){
            $where['status'] = $status;
        }
        $column = ['*'];
        if (!empty($keywords)){
            $keyword = [$keywords => ['title', 'address', 'tenancy', 'leasing_title', 'unit_title', 'toward_title', 'tenancy']];
            if (!$list = HouseDetailsViewRepository::search($keyword,$where,$column,$page,$page_num,'id','desc')){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = HouseDetailsViewRepository::getList($where,$column,'id','desc',$page,$page_num)){
                $this->setError('获取失败！');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        foreach ($list['data'] as &$value){
            #处理地址
            list($area_address,$lng,$lat) = $this->makeAddress($value['area_code'],$value['address']);
            $value['area_address']  = $area_address;
            $value['lng']           = $lng;
            $value['lat']           = $lat;
            #处理价格
            $value['rent_tenancy']          = '¥'. $value['rent'] .'/'. HouseEnum::getTenancy($value['tenancy']);

            $value['decoration_title'] = HouseEnum::getDecoration($value['decoration']);
            $image_list = CommonImagesRepository::getList(['id' => ['in',explode(',',$value['image_ids'])]]);
            $value['images']        = array_column($image_list,'img_url');
            $value['category_title']      = HouseEnum::getCategory($value['category']);
            $value['publisher_title']     = HouseEnum::getPublisher($value['publisher']);
            $value['facilities']    = HouseFacilitiesRepository::getFacilitiesList(explode(',',$value['facilities_ids']),['id','title','icon_id']);
            $value['created_at'] = date('Y-m-d H:i:s',$value['created_at']);
            $value['updated_at'] = date('Y-m-d H:i:s',$value['updated_at']);
            $value['deleted_at'] = $value['deleted_at'] ==0 ? '':date('Y-m-d H:i:s',$value['deleted_at']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    public function getHomeList($request)
    {

    }
}
            