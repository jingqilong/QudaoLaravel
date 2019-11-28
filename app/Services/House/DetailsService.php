<?php
namespace App\Services\House;


use App\Enums\HouseEnum;
use App\Enums\MemberEnum;
use App\Repositories\CommonAreaRepository;
use App\Repositories\CommonImagesRepository;
use App\Repositories\HouseDetailsRepository;
use App\Repositories\HouseFacilitiesRepository;
use App\Repositories\MemberBaseRepository;
use App\Repositories\OaEmployeeRepository;
use App\Services\BaseService;
use App\Services\Common\SmsService;
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
            'leasing'       => $request['leasing'],
            'decoration'    => $request['decoration'],
            'height'        => $request['height'],
            'area'          => $request['area'],
            'image_ids'     => $request['image_ids'],
            'storey'        => $request['storey'],
            'unit'          => $request['unit'],
            'condo_name'    => $request['condo_name'] ?? '',
            'toward'        => $request['toward'],
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
        $column = ['id','title','area_code','address','describe','rent','tenancy','leasing','decoration','height','area'
            ,'image_ids','storey','unit','condo_name','toward','category','publisher','facilities_ids'];
        if (!$house = HouseDetailsRepository::getOne(['id' => $id],$column)){
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
        $house['area']          = $house['area'] .'㎡'   ;
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
            'leasing'       => $request['leasing'],
            'decoration'    => $request['decoration'],
            'height'        => $request['height'],
            'area'          => $request['area'],
            'image_ids'     => $request['image_ids'],
            'storey'        => $request['storey'],
            'unit'          => $request['unit'],
            'condo_name'    => $request['condo_name'] ?? '',
            'toward'        => $request['toward'],
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
            $keyword = [$keywords => ['title', 'address', 'tenancy', 'leasing', 'unit', 'toward', 'tenancy']];
            if (!$list = HouseDetailsRepository::search($keyword,$where,$column,$page,$page_num,'id','desc')){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = HouseDetailsRepository::getList($where,$column,'id','desc',$page,$page_num)){
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
            $value['area_code']     = rtrim($value['area_code'],',');
            $value['lng']           = $lng;
            $value['lat']           = $lat;
            #处理价格
            $value['rent_tenancy']          = '¥'. $value['rent'] .'/'. HouseEnum::getTenancy($value['tenancy']);

            $value['decoration_title'] = HouseEnum::getDecoration($value['decoration']);
            $image_list = CommonImagesRepository::getList(['id' => ['in',explode(',',$value['image_ids'])]],['id','img_url']);
            $value['images']              = $image_list;
            $value['category_title']      = HouseEnum::getCategory($value['category']);
            $value['publisher_title']     = HouseEnum::getPublisher($value['publisher']);
            $value['publisher_name']      = $this->getPublisherName($value['publisher'],$value['publisher_id']);
            $value['facilities']    = HouseFacilitiesRepository::getFacilitiesList(explode(',',$value['facilities_ids']),['id','title','icon_id']);
            $value['facilities_ids']     = rtrim($value['facilities_ids'],',');
            $value['created_at'] = date('Y-m-d H:i:s',$value['created_at']);
            $value['updated_at'] = date('Y-m-d H:i:s',$value['updated_at']);
            $value['deleted_at'] = $value['deleted_at'] ==0 ? '':date('Y-m-d H:i:s',$value['deleted_at']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 获取首页列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getHomeList($request)
    {
        $keywords   = $request['keywords'] ?? '';
        $area_code  = $request['area_code'] ?? '';
        $category   = $request['category'] ?? '';
        $rent_range = $request['rent_range'] ?? '';
        $rent_order = $request['rent_order'] ?? '';
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $where      = ['deleted_at' => 0,'status' => HouseEnum::PASS];
        $order      = 'id';
        $desc_asc   = 'desc';
        if (!empty($area_code)){
            $where['area_code'] = ['like','%'.$area_code.',%'];
        }
        if (!empty($category)){
            $where['category'] = $category;
        }
        if (!empty($rent_range)){
            $range = explode('-',$rent_range);
            $where['rent'] = ['range',[reset($range),end($range)]];
        }
        if (!empty($rent_order)){
            $order      = 'rent';
            $desc_asc   = $rent_order == 1 ? 'asc' : 'desc';
        }
        $column = ['id','title','area_code','describe','rent','tenancy','leasing','decoration','image_ids','storey','unit','condo_name','toward','category'];
        if (!empty($keywords)){
            $keyword = [$keywords => ['title','leasing', 'unit', 'toward']];
            if (!$list = HouseDetailsRepository::search($keyword,$where,$column,$page,$page_num,$order,$desc_asc)){
                $this->setError('获取失败！');
                return false;
            }
        }else{
            if (!$list = HouseDetailsRepository::getList($where,$column,$order,$desc_asc,$page,$page_num)){
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
            list($area_address,$lng,$lat) = $this->makeAddress($value['area_code'],'',3);
            $value['area_address']  = $area_address;
            $value['storey']        = $value['storey'].'层';
            #处理价格
            $value['rent_tenancy']          = '¥'. $value['rent'] .'/'. HouseEnum::getTenancy($value['tenancy']);
            $value['decoration'] = HouseEnum::getDecoration($value['decoration']);
            $image_list = CommonImagesRepository::getList(['id' => ['in',explode(',',$value['image_ids'])]]);
            $value['images']        = array_column($image_list,'img_url');
            $value['category']      = HouseEnum::getCategory($value['category']);
            unset($value['rent'],$value['image_ids'],$value['area_code'],$value['tenancy']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 审核房源
     * @param $id
     * @param $audit
     * @return bool
     */
    public function auditHouse($id, $audit)
    {
        if (!$house = HouseDetailsRepository::getOne(['id' => $id])){
            $this->setError('房源不存在！');
            return false;
        }
        if ($house['status'] > HouseEnum::PENDING){
            $this->setError('预约已审核！');
            return false;
        }
        $status = ($audit == 1) ? HouseEnum::PASS : HouseEnum::NOPASS;
        if (!HouseDetailsRepository::getUpdId(['id' => $id],['status' => $status])){
            $this->setError('审核失败！');
            return false;
        }
        #通知用户
        if ($house['publisher'] == HouseEnum::PERSON)
        if ($member = MemberBaseRepository::getOne(['id' => $house['publisher_id']])){
            $member_name = !empty($member['ch_name']) ? $member['ch_name'] : (!empty($member['en_name']) ? $member['en_name'] : (substr($member['mobile'],-4)));
            $member_name = $member_name . MemberEnum::getSex($member['sex']);
            #短信通知
            if (!empty($member['mobile'])){
                $smsService = new SmsService();
                $sms_template = [
                    HouseEnum::PASS         => '尊敬的'.$member_name.'您好！您发布的房源已经通过审核，现已上架，感谢您的使用！',
                    HouseEnum::NOPASS       => '尊敬的'.$member_name.'您好！您发布的房源由于信息不完善未通过审核，完善信息后可再次发起审核！',
                ];
                $smsService->sendContent($member['mobile'],$sms_template[$status]);
            }
        }
        $this->setMessage('审核成功！');
        return true;
    }

    /**
     * 获取发布人手机号
     * @param $publisher
     * @param $publisher_id
     * @return string|null
     */
    public function getPublisherName($publisher, $publisher_id){
        $name = '';
        if ($publisher == HouseEnum::PERSON){
            $name = MemberBaseRepository::getField(['id' => $publisher_id],'mobile');
        }
        if ($publisher == HouseEnum::PLATFORM){
            $name = OaEmployeeRepository::getField(['id' => $publisher_id],'mobile');
        }
        return $name;
    }
}
            