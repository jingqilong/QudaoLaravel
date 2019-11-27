<?php
namespace App\Services\Member;


use App\Repositories\MemberAddressRepository;
use App\Services\BaseService;
use App\Services\Shop\OrderRelateService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddressService extends BaseService
{
    use HelpTrait;

    public $auth;

    /**
     * PrizeService constructor.
     * @param $auth
     */
    public function __construct()
    {
        $this->auth = Auth::guard('member_api');
    }


    /**
     * 用户添加地址
     * @param $request
     * @return bool
     */
    public function addAddress($request)
    {
        $memberInfo = $this->auth->user();
        $member_id  = $memberInfo->m_id;
        $default    = $request['default'] ?? 0;
        $add_arr = [
            'member_id'      => $member_id,
            'name'           => $request['name'],
            'mobile'         => $request['mobile'],
            'area_code'      => $request['area_code'],
            'address'        => $request['address'],
        ];
        if (MemberAddressRepository::exists($add_arr)){
            $this->setError('地址已经存在!');
            return false;
        }
        DB::beginTransaction();
        if ($default == 1){
            if (MemberAddressRepository::getOne($add_arr)){
                if (!MemberAddressRepository::getUpdId(['default' => 1,'member_id' =>$member_id,'deleted_at' => 0],['default' => 0])){
                    DB::rollBack();
                    $this->setError('添加失败!');
                    return false;
                }
            }
        }
        $add_arr['created_at']      =  time();
        $add_arr['updated_at']      =  time();
        $add_arr['default']         =  $default;
        if (!MemberAddressRepository::getAddId($add_arr)){
            DB::rollBack();
            $this->setError('添加失败!');
            return false;
        }
        DB::commit();
        $this->setMessage('添加成功!');
        return true;
    }

    /**
     * 用户删除地址
     * @param $request
     * @return bool
     */
    public function delAddress($request)
    {
        if (!MemberAddressRepository::exists(['id' => $request['id'],'deleted_at' => 0])){
            $this->setError('地址不存在!');
            return false;
        }
        if (!MemberAddressRepository::getUpdId(['id' => $request['id']],['deleted_at' => time()])){
            $this->setError('删除失败!');
            return false;
        }
        $this->setMessage('删除成功!');
        return true;
    }

    /**
     * 用户修改地址
     * @param $request
     * @return bool
     */
    public function editAddress($request)
    {
        $memberInfo = $this->auth->user();
        $member_id  = $memberInfo->m_id;
        $default    = $request['default'] ?? 0;
        $upd_arr = [
            'member_id'      => $member_id,
            'name'           => $request['name'],
            'mobile'         => $request['mobile'],
            'area_code'      => $request['area_code'],
            'address'        => $request['address'],
            'default'        => $default,
        ];
        if (MemberAddressRepository::exists($upd_arr)){
            $this->setError('地址已经存在!');
            return false;
        }
        DB::beginTransaction();
        if ($default == 1){
            if (MemberAddressRepository::getOne($upd_arr)) {
                if (!MemberAddressRepository::getUpdId(['default' => 1, 'member_id' => $member_id, 'deleted_at' => 0], ['default' => 0])) {
                    DB::rollBack();
                    $this->setError('修改失败!');
                    return false;
                }
            }
        }
        $add_arr['updated_at']      =  time();
        if (!MemberAddressRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            DB::rollBack();
            $this->setError('修改失败!');
            return false;
        }
        DB::commit();
        $this->setMessage('修改成功!');
        return true;
    }

    /**
     * 用户获取地址
     * @param $request
     * @return array|bool|mixed|null
     */
    public function addressList($request)
    {
        $memberInfo   = $this->auth->user();
        $page         = $request['page'] ?? 1;
        $page_num     = $request['page_num'] ?? 20;
        $column       = ['name','mobile','area_code','address','default'];
        $where        = ['deleted_at' => 0,'member_id' => $memberInfo->m_id];
        if (!$list = MemberAddressRepository::getList($where,$column,null,null,$page,$page_num)){
            $this->setError('获取失败!');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('暂无数据!');
            return $list;
        }
        $list = $this->removePagingField($list);
        $value['area_address'] = '';
        foreach ($list['data'] as &$value){
            #处理地址
            list($area_address) = $this->makeAddress($value['area_code'],'');
            $value['area_address']  = $area_address;
        }
        $this->setMessage('查找成功');
        return $list;
    }

    /**
     * oa获取用户地址列表
     * @param $request
     * @return array|bool|mixed|null
     */
    public function listAddress($request)
    {
        $page         = $request['page'] ?? 1;
        $page_num     = $request['page_num'] ?? 20;
        $column       = ['name','mobile','area_code','address','default','created_at','updated_at'];
        $where        = ['deleted_at' => 0];
        $keywords     = $request['keywords'] ?? null;
        $asc          = isset($request['asc']) ? ($request['asc'] == 1 ? 'asc' : 'desc' ) : 'asc';
        if (!empty($keywords)){
            $keyword = [$keywords => ['name','mobile']];
            if (!$list = MemberAddressRepository::search($keyword,$where,$column,$page,$page_num,'id',$asc)){
                $this->setError('获取失败!');
                return false;
            }
        }else{
            if (!$list = MemberAddressRepository::getList($where,$column,'id',$asc,$page,$page_num)){
                $this->setError('获取失败!');
                return false;
            }
        }
        if (empty($list['data'])){
            $this->setMessage('暂无数据!');
            return $list;
        }
        $list = $this->removePagingField($list);
        $value['area_address'] = '';
        foreach ($list['data'] as &$value){
            #处理地址
            list($area_address) = $this->makeAddress($value['area_code'],'');
            $value['area_address']  = $area_address;
        }
        $this->setMessage('获取成功');
        return $list;
    }

    /**
     * 获取用户默认地址，没有则获取第一条地址，如果没有添加地址则返回空
     * @param $member_id
     * @return array|null
     */
    protected function getDefaultAddress($member_id){
        $where  = ['member_id' => $member_id];
        $column = ['id','name','mobile','area_code','address'];
        if (!$address = MemberAddressRepository::getOne(array_merge($where,['default' => 1]),$column)){
            if (!$address = MemberAddressRepository::getOne($where,$column)){
                $this->setMessage('没有默认地址！');
                return [];
            }
        }
        list($area_address)         = $this->makeAddress($address['area_code'],$address['address']);
        $address['area_address']    = $area_address;
        $address['free_shipping']   = 0;#默认不包邮
        $area_code = explode(',',trim($address['area_code'],','));
        if (array_intersect($area_code,OrderRelateService::$free_shipping_area_code)){
            $address['free_shipping'] = 1;
        }
        unset($address['area_code'],$address['address']);
        $this->setMessage('获取成功！');
        return $address;
    }
}
            