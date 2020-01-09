<?php
namespace App\Services\House;


use App\Enums\CollectTypeEnum;
use App\Enums\CommonHomeEnum;
use App\Enums\HouseEnum;
use App\Enums\MemberEnum;
use App\Enums\ProcessCategoryEnum;
use App\Repositories\CommonAreaRepository;
use App\Repositories\CommonImagesRepository;
use App\Repositories\HouseDetailsRepository;
use App\Repositories\HouseFacilitiesRepository;
use App\Repositories\MemberBaseRepository;
use App\Repositories\MemberCollectRepository;
use App\Repositories\OaEmployeeRepository;
use App\Services\BaseService;
use App\Services\Common\AreaService;
use App\Services\Common\HomeBannersService;
use App\Services\Common\ImagesService;
use App\Services\Common\SmsService;
use App\Traits\BusinessTrait;
use App\Traits\HelpTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DetailsService extends BaseService
{
    use HelpTrait,BusinessTrait;

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
            'longitude'     => $request['longitude'],
            'latitude'      => $request['latitude'],
            'address'       => $request['address'],
            'describe'      => $request['describe'] ?? '',
            'rent'          => $request['rent'],
            'tenancy'       => $request['tenancy'],
            'leasing'       => $request['leasing'],
            'decoration'    => $request['decoration'],
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
            'status'        => $publisher == HouseEnum::PERSON ? HouseEnum::PENDING : HouseEnum::PASS,
        ];
        if (HouseDetailsRepository::exists($add_arr)){
            $this->setError('房产信息已添加！');
            return false;
        }
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();
        DB::beginTransaction();
        if (!$id = HouseDetailsRepository::getAddId($add_arr)){
            $this->setError('发布失败！');
            DB::rollBack();
            return false;
        }
        #如果是用户发布房源，则开启流程
        if ($publisher == HouseEnum::PERSON){
            $start_process_result = $this->addNewProcessRecord($id,ProcessCategoryEnum::HOUSE_RELEASE);
            if (100 == $start_process_result['code']){
                $this->setError('预约失败，请稍后重试！');
                DB::rollBack();
                return false;
            }
        }
        DB::commit();
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
        $auth = Auth::guard('member_api');
        $member = $auth->user();
        if (!HouseDetailsRepository::exists(['id' => $id,'deleted_at' => 0])){
            $this->setError('房产信息不存在！');
            return false;
        }
        $column = ['id','title','area_code','address','describe','longitude','latitude','rent','tenancy','leasing','decoration','area'
            ,'image_ids','storey','unit','condo_name','toward','category','publisher','facilities_ids'];
        if (!$house = HouseDetailsRepository::getOne(['id' => $id],$column)){
            $this->setError('获取失败！');
            return false;
        }
        #处理地址
        list($area_address) = $this->makeAddress($house['area_code'],$house['address']);
        list($district_name) = $this->makeAddress($house['area_code'],'',3);
        $house['district']  = $district_name;
        $house['area_address']  = $area_address;
        #处理价格
        $house['rent']          = '¥'. $house['rent'] .'/'. HouseEnum::getTenancy($house['tenancy']);

        $house['decoration'] = HouseEnum::getDecoration($house['decoration']);
        $house['area']          = $house['area'] .'㎡'   ;
        $image_list = CommonImagesRepository::getList(['id' => ['in',explode(',',$house['image_ids'])]]);
        $house['images']        = array_column($image_list,'img_url');
        $house['storey']        = $house['storey'] .'层';
        $house['category']      = HouseEnum::getCategory($house['category']);
        $house['publisher']     = HouseEnum::getPublisher($house['publisher']);
        $house['facilities']    = HouseFacilitiesRepository::getFacilitiesList(explode(',',$house['facilities_ids']));
        #是否收藏
        $house['is_collect'] = 0;
        if (MemberCollectRepository::exists(['type' => CollectTypeEnum::HOUSE,'target_id' => $house['id'],'member_id' => $member->id,'deleted_at' => 0])){
            $house['is_collect'] = 1;
        }
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
            $this->setError('该房源已上架，无法删除！');
            return false;
        }
        //检查商品是否为banner展示
        $homeBannerService = new HomeBannersService();
        if ($homeBannerService->deleteBeforeCheck(CommonHomeEnum::HOUSE,$id) == false){
            $this->setError($homeBannerService->error);
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
            'longitude'     => $request['longitude'] ?? '',
            'latitude'      => $request['latitude'] ?? '',
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
        $employee = Auth::guard('oa_api')->user();
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
            $value['area_address']      = $this->getAreaName($value['area_code'],0,3);
            $value['area_code']         = rtrim($value['area_code'],',');
            #处理价格
            $value['rent_tenancy']      = '¥'. $value['rent'] .'/'. HouseEnum::getTenancy($value['tenancy']);
            $value['decoration_title']  = HouseEnum::getDecoration($value['decoration']);
            $image_list                 = CommonImagesRepository::getList(['id' => ['in',explode(',',$value['image_ids'])]],['id','img_url']);
            $value['images']            = $image_list;
            $value['category_title']    = HouseEnum::getCategory($value['category']);
            $value['publisher_title']   = HouseEnum::getPublisher($value['publisher']);
            $publisher                  = $this->getPublisher($value['publisher'],$value['publisher_id']);
            $value['publisher_name']    = $publisher['name'];
            $value['publisher_mobile']  = $publisher['mobile'];
            $value['facilities']        = HouseFacilitiesRepository::getFacilitiesList(explode(',',$value['facilities_ids']),['id','title','icon_id']);
            $value['facilities_ids']    = rtrim($value['facilities_ids'],',');
            $value['status_title']      = HouseEnum::getStatus($value['status']);
            $value['created_at']        = date('Y-m-d H:i:s',$value['created_at']);
            $value['updated_at']        = date('Y-m-d H:i:s',$value['updated_at']);
            unset($value['deleted_at']);
            #获取流程信息
            $value['progress']          = $this->getBusinessProgress($value['id'],ProcessCategoryEnum::HOUSE_RELEASE,$employee->id);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 房源审核详情
     * @param $id
     * @return array|bool
     */
    public function houseAuditDetail($id){
        $employee   = Auth::guard('oa_api')->user();
        $column     = ['id','title','area_code','longitude','latitude','address','describe','rent','tenancy','leasing','decoration',
        'area','image_ids','storey','unit','condo_name','toward','category','publisher','publisher_id','facilities_ids','status','created_at','updated_at'
        ];
        if (!$house = HouseDetailsRepository::getOne(['id' => $id,'deleted_at' => 0],$column)){
            $this->setError('房源不存在！');
            return false;
        }
        $house['area_address']      = $this->getAreaName($house['area_code'],0,3);
//        $house['area_code']         = rtrim($house['area_code'],',');
        #处理价格
        $house['rent_tenancy']      = '¥'. $house['rent'] .'/'. HouseEnum::getTenancy($house['tenancy']);
        $house['decoration']        = HouseEnum::getDecoration($house['decoration']);
        $house['category']          = HouseEnum::getCategory($house['category']);
        $publisher                  = $this->getPublisher($house['publisher'],$house['publisher_id']);
        $house['publisher_name']    = $publisher['name'];
        $house['publisher_mobile']  = $publisher['mobile'];
        $house['publisher']         = HouseEnum::getPublisher($house['publisher']);
        $house['status']            = HouseEnum::getStatus($house['status']);
        $house['created_at']        = date('Y-m-d H:i:s',$house['created_at']);
        $house['updated_at']        = date('Y-m-d H:i:s',$house['updated_at']);
        $image_list                 = CommonImagesRepository::getList(['id' => ['in',explode(',',$house['image_ids'])]],['img_url']);
        $house['images']            = empty($image_list) ? [] : Arr::flatten($image_list);
        $house['facilities']        = HouseFacilitiesRepository::getFacilitiesList(explode(',',$house['facilities_ids']),['title','icon_id']);
        unset($house['area_code'],$house['rent'],$house['tenancy'],$house['image_ids'],$house['facilities_ids'],$house['publisher_id'],$house['rent'],$house['rent']);
        return $this->getBusinessDetailsProcess($house,ProcessCategoryEnum::LOAN_RESERVATION,$employee->id);
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
        $_order     = $request['order'] ?? '';
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
            if (empty(end($range))){
                $where['rent'] = ['>',reset($range)];
            }else{
                $where['rent'] = ['range',[reset($range),end($range)]];
            }
        }
        if (!empty($_order)){
            switch ($_order){
                case 1:
                    $order      = 'rent';
                    $desc_asc   = 'asc';
                    break;
                case 2:
                    $order      = 'rent';
                    $desc_asc   = 'desc';
                    break;
                case 3:
                    $order      = 'area';
                    $desc_asc   = 'asc';
                    break;
                case 4:
                    $order      = 'area';
                    $desc_asc   = 'desc';
                    break;
            }
        }
        $column = ['id','title','area_code','area','describe','rent','tenancy','leasing','decoration','image_ids','storey','unit','condo_name','toward','category'];
        if (!empty($keywords)){
            $keyword = [$keywords => ['title','leasing', 'unit', 'toward','condo_name']];
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
        $list           = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        foreach ($list['data'] as &$value){
            #处理地址
            list($area_address) = $this->makeAddress($value['area_code'],'',3);
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
     * 获取房产首页接口
     * @return mixed
     */
    public function getHouseHomeList()
    {
        $code           = new AreaService();
        #获取精品商铺
        $res['shops']       = $this->categoryList(['category' => HouseEnum::SHOP]);
        #获取住宅
        $res['residence']   = $this->categoryList(['category' => HouseEnum::RESERVATION]);
        #获取写字楼
        $res['office']      = $this->categoryList(['category' => HouseEnum::OFFICE]);
        #获取区域
        $res['area']        = $code->getAreaList(310100,['code' => ['notIn',['310108','310230']]]);
        $this->setMessage('获取成功！');
        return $res;
    }

    protected function categoryList($data)
    {
        $category = $data['category'] ?? null;
        $where    = ['deleted_at' => 0,'status' => HouseEnum::PASS];
        $page     = '1';
        $page_num = '4';
        $column   = ['id','title','rent','tenancy','image_ids'];
        if (!empty($category)){
            $where['category'] = $category;
        }
        if (!$list = HouseDetailsRepository::getList($where,$column,'id','desc',$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list = $this->removePagingField($list);
        $list['data'] = ImagesService::getListImagesConcise($list['data'],['image_ids' => 'single']);
        foreach ($list['data'] as &$value){
            #处理价格
            $value['rent_tenancy']   = '¥'. $value['rent'] .'/'. HouseEnum::getTenancy($value['tenancy']);
        }
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
    public function getPublisher($publisher, $publisher_id){
        $result = ['name' => '','mobile' => ''];
        if ($publisher == HouseEnum::PERSON){
            $member = MemberBaseRepository::getOne(['id' => $publisher_id],['mobile','ch_name']);
            $result['name']     = $member['ch_name'] ?? '';
            $result['mobile']   = $member['mobile'] ?? '';
        }
        if ($publisher == HouseEnum::PLATFORM){
            $employee = OaEmployeeRepository::getOne(['id' => $publisher_id],['mobile','real_name']);
            $result['name']     = $employee['real_name'] ?? '';
            $result['mobile']   = $employee['mobile'] ?? '';
        }
        return $result;
    }

    /**
     * 地域选房列表
     * @param $request
     * @return bool
     */
    public function getCodeList($request)
    {
        $area_code  = $request['area_code'] ?? '';
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $where      = ['deleted_at' => 0,'status' => HouseEnum::PASS];
        $order      = 'id';
        $desc_asc   = 'desc';
        $column = ['id','title','area_code','address','describe','rent','tenancy','leasing','decoration','height','area'
        ,'image_ids','storey','unit','condo_name','toward','category','publisher','facilities_ids'];
        if (!CommonAreaRepository::exists(['code' => $area_code])){
            $this->setError('无效的区域!');
            return false;
        }
        if (!empty($area_code)){
            $where['area_code'] = ['like',$area_code.',%'];
        }
        if (!$list = HouseDetailsRepository::getList($where,$column,$order,$desc_asc,$page,$page_num)){
            $this->setError('获取失败!');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data']       = ImagesService::getListImagesConcise($list['data'],['image_ids' => 'several']);
        foreach ($list['data'] as &$value){
            #处理地址
            list($area_address) = $this->makeAddress($value['area_code'],'',3);
            $value['area_address']  = $area_address;
            $value['storey']        = $value['storey'].'层';
            #处理价格
            $value['rent_tenancy']          = '¥'. $value['rent'] .'/'. HouseEnum::getTenancy($value['tenancy']);
            $value['decoration'] = HouseEnum::getDecoration($value['decoration']);
            $value['category']      = HouseEnum::getCategory($value['category']);
            unset($value['rent'],$value['image_ids'],$value['area_code'],$value['tenancy']);
        }
        $this->setMessage('获取成功!');
        return $list;
    }

    /**
     * 获取我的房源列表
     * @param $request
     * @return bool|mixed|null
     */
    public function getMyHouseList($request)
    {
        $member_id = Auth::guard('member_api')->user()->id;
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $where      = ['deleted_at' => 0,'publisher' => HouseEnum::PERSON,'publisher_id' => $member_id];
        $order      = 'id';
        $desc_asc   = 'desc';
        $column = ['id','title','area_code','rent','tenancy','leasing','decoration','area','image_ids','storey','condo_name','category','status'];
        if (!$list = HouseDetailsRepository::getList($where,$column,$order,$desc_asc,$page,$page_num)){
            $this->setError('获取失败！');
            return false;
        }
        $list           = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据！');
            return $list;
        }
        $list['data'] = ImagesService::getListImages($list['data'],['image_ids' => 'single'],true);
        foreach ($list['data'] as &$value){
            #处理区域
            $value['area_address']  = $this->getAreaName($value['area_code'],3);;
            $value['storey']        = $value['storey'].'层';
            #处理价格
            $value['rent_tenancy']  = '¥'. $value['rent'] .'/'. HouseEnum::getTenancy($value['tenancy']);
            $value['decoration']    = HouseEnum::getDecoration($value['decoration']);
            $value['category']      = HouseEnum::getCategory($value['category']);
            $value['status_title']  = HouseEnum::getStatus($value['status']);
            unset($value['rent'],$value['image_ids'],$value['area_code'],$value['tenancy']);
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 获取我的房源状态
     * @param $id
     * @return bool|null
     */
    public function getMyHouseStatus($id)
    {
        $member = Auth::guard('member_api')->user();
        $member_id =  $member->id;
        if (!$house = HouseDetailsRepository::getOne(['id' => $id,'publisher_id' => $member_id,'publisher' => HouseEnum::PERSON],['id','title','area','decoration','category','area_code','condo_name','status'])){
            $this->setError('房源不存在！');
            return false;
        }
        list($area_address,$lng,$lat) = $this->makeAddress($house['area_code'],'',3);
        $house['decoration']    = HouseEnum::getDecoration($house['decoration']);
        $house['category']      = HouseEnum::getCategory($house['category']);
        $house['area_address']  = $area_address;
        $house['status_title']  = HouseEnum::getStatus($house['status']);
        $house['mobile']        = $member->mobile;
        unset($house['area_code'],$house['area_code']);
        $this->setMessage('获取成功！');
        return $house;
    }
}
            