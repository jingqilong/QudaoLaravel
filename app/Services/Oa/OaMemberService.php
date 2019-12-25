<?php


namespace App\Services\Oa;


use App\Enums\CommonHomeEnum;
use App\Enums\MemberEnum;
use App\Repositories\MemberBaseRepository;
use App\Repositories\MemberGradeRepository;
use App\Repositories\MemberGradeViewRepository;
use App\Repositories\MemberInfoRepository;
use App\Repositories\MemberOaListViewRepository;
use App\Repositories\MemberPersonalServiceRepository;
use App\Repositories\MemberPreferenceRepository;
use App\Repositories\OaGradeViewRepository;
use App\Services\BaseService;
use App\Services\Common\HomeBannersService;
use App\Services\Common\ImagesService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OaMemberService extends BaseService
{
    use HelpTrait;
    protected $auth;

    /**
     * OaMemberService constructor.
     */
    public function __construct()
    {
        $this->auth = Auth::guard('oa_api');
    }


    /**
     * 获取成员列表 (拆表后 已修改)
     * @param array $data
     * @return mixed
     */
    /*public function getMemberList(array $data)
    {
        if (empty($data['asc'])) $data['asc'] = 1;
        $is_home_detail = $data['is_home_detail'] ?? null;
        $page           = $data['page'] ?? 1;
        $page_num       = $data['page_num'] ?? 20;
        $asc            = $data['asc'] ==  1 ? 'asc' : 'desc';
        $keywords       = $data['keywords'] ?? null;
        $column         = ['id','card_no','ch_name','en_name','is_recommend','is_home_detail','sex','mobile','grade','position','address','employer','img_url','title','category','status','hidden','created_at'];
        $where          = ['deleted_at' => 0];
        if (!empty($is_home_detail)) $where['is_home_detail'] = $is_home_detail;
        if (!empty($keywords)){
            $keyword        = [$keywords => ['ch_name','en_name','category','card_no','mobile','grade']];
            if(!$member_list = MemberGradeViewRepository::search($keyword,$where,$column,$page,$page_num,'created_at',$asc)){
                $this->setMessage('暂无成员信息！');
                return [];
            }
        }else {
            if(!$member_list = MemberGradeViewRepository::getList($where,$column,'created_at',$asc,$page,$page_num)){
                $this->setMessage('没有查到该成员！');
                return [];
            }
        }
        $this->removePagingField($member_list);
        if (empty($member_list['data'])) {
            $this->setMessage('没有成员!');
        }
        foreach ($member_list['data'] as &$value){
            $value['is_recommend']  = $value['is_recommend'] == 0 ? 0 : 1;
            $value['grade_name']    = MemberEnum::getGrade($value['grade'],'普通成员');
            $value['category_name'] = MemberEnum::getCategory($value['category'],'普通成员');
            $value['sex_name']      = MemberEnum::getSex($value['sex'],'未设置');
            $value['status_name']   = MemberEnum::getStatus($value['status'],'成员');
            $value['hidden_name']   = MemberEnum::getHidden($value['hidden'],'显示');
        }
        $this->setMessage('获取成功！');
        return $member_list;
    }*/


    /**
     * 获取成员列表 (拆表后 已修改) （2）
     * @param array $data
     * @return mixed
     */
    public function getMemberList(array $data)
    {
        if (empty($data['asc'])) $data['asc'] = 1;
        $is_home_detail = $data['is_home_detail'] ?? null;
        $page           = $data['page'] ?? 1;
        $page_num       = $data['page_num'] ?? 20;
        $asc            = $data['asc'] ==  1 ? 'asc' : 'desc';
        $keywords       = $data['keywords'] ?? null;
        $where          = ['deleted_at' => 0];
        $column = ['id','card_no','ch_name','sex','mobile','address','status','hidden','created_at',
            'end_at','is_recommend','is_home_detail','grade','title','category'];
        if (!empty($is_home_detail)) $where['is_home_detail'] = $is_home_detail;
        if (!empty($keywords)) {
            $keyword = [$keywords => ['card_no', 'mobile', 'ch_name', 'grade', 'category']];
            if (!$list = MemberOaListViewRepository::search($keyword, $where, $column, $page, $page_num, 'created_at', $asc)) {
                $this->setError('获取失败!');
                return false;
            }
        }else{
            if (!$list = MemberOaListViewRepository::getList($where, $column, 'created_at', $asc, $page, $page_num)) {
                $this->setError('获取失败!');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            $this->setMessage('获取成功!');
            return [];
        }
        foreach ($list['data'] as &$value){
            $value['is_recommend']  = $value['is_recommend'] == 0 ? 0 : 1;
            $value['grade_name']    = MemberEnum::getGrade($value['grade'],'普通成员');
            $value['category_name'] = MemberEnum::getCategory($value['category'],'普通成员');
            $value['sex_name']      = MemberEnum::getSex($value['sex'],'未设置');
            $value['status_name']   = MemberEnum::getStatus($value['status'],'成员');
            $value['hidden_name']   = MemberEnum::getHidden($value['hidden'],'显示');
        }
        $this->setMessage('获取成功！');
        return $list;
    }

    /**
     * 获取成员信息 (拆表后 已修改)
     * @param string $id
     * @return bool|null
     */
    public function getMemberInfo(string $id)
    {
        if (!$member = MemberGradeViewRepository::getOne(['id' => $id,'deleted_at' => 0])){
            $this->setError('用户不存在!');
            return false;
        }
        $member['grade_name']    = MemberEnum::getGrade($member['grade'],'普通成员');
        $member['category_name'] = MemberEnum::getCategory($member['category'],'普通成员');
        $member['is_recommend']  = $member['is_recommend'] == 0 ? 0 : 1;
        $member['sex_name']      = MemberEnum::getSex($member['sex'],'未设置');
        $member['status_name']   = MemberEnum::getStatus($member['status'],'成员');
        $member['hidden_name']   = MemberEnum::getHidden($member['hidden'],'显示');
        $member['created_at']    = date('Y-m-d H:i:s',$member['created_at']);
        $member['birthday']      = date('Y-m-d',strtotime($member['birthday']));
        if (empty($member['birthday'])) $member['birthday'] = '';
        if (0 == $member['end_at']) $member['end_at_name'] = MemberEnum::getExpiration(MemberEnum::PERMANENT,'永久有效'); else{
            $member['end_at_name']    = date('Y-m-d H:i:s',$member['end_at']);
        }
        $this->setMessage('获取用户信息成功');
        return $member;
    }

    /**
     * 成员软删除 (拆表后 已修改)
     * @param string $id
     * @return bool|null
     */
    public function delMember(string $id)
    {
        if (!MemberGradeViewRepository::exists(['id' => $id])){
            $this->setError('用户信息不存在!');
            return false;
        }
        //检查商品是否为banner展示
        $homeBannerService = new HomeBannersService();
        if ($homeBannerService->deleteBeforeCheck(CommonHomeEnum::MEMBER,$id) == false){
            $this->setError($homeBannerService->error);
            return false;
        }
        if (!MemberBaseRepository::getUpdId(['id' => $id],['deleted_at' => time()])){
            $this->setError('删除成员失败!');
            return false;
        }
        $this->setMessage('删除成员成功');
        return true;
    }

    /**
     * 成员禁用or激活 (拆表后 已修改) (优化)
     * @param $request
     * @return bool|null
     */
    public function setMemberStatus($request)
    {
        if (empty($request['id'])){
            $this->setError('会员ID为空！');
            return false;
        }
        if (!MemberBaseRepository::exists(['id' =>$request['id']])){
            $this->setError('用户不存在!');
            return false;
        }
        if (!MemberBaseRepository::getUpdId(['id' => $request['id']],['hidden' => $request['hidden']])){
            $this->setError('设置失败!');
            return false;
        }
        $this->setMessage('设置成功!');
        return true;
    }


    /**
     * 添加成员 (拆表后 已修改) （优化）
     * @param $request
     * @return bool|null
     */
    public function addMember($request)
    {
        if (MemberBaseRepository::exists(['card_no' => $request['card_no']])){
            $this->setError('会员卡号已存在!');
            return false;
        }
        DB::beginTransaction();
        if (!$member_id = MemberBaseRepository::addMemberBase($request)){
            DB::rollBack();
            $this->setError('添加失败，请重试！');
            return false;
        }
        if (!$member_id = MemberInfoRepository::addMemberInfo($request,$member_id)){
            DB::rollBack();
            $this->setError('添加失败，请重试！');
            return false;
        }
        if (!MemberPersonalServiceRepository::addMemberService($request,$member_id)){
            DB::rollBack();
            $this->setError('添加失败，请重试！');
            return false;
        }
        if (!MemberGradeRepository::addMemberGrade($request,$member_id)){
            DB::rollBack();
            $this->setError('添加失败，请重试！');
            return false;
        }
        if (!MemberPreferenceRepository::addMemberPreference($request,$member_id)){
            DB::rollBack();
            $this->setError('添加失败，请重试！');
            return false;
        }
        DB::commit();
        $this->setMessage('添加成功!');
        return true;
    }


    /**
     * 更新完善成员信息 (拆表后  已修改)
     * @param $request
     * @return bool|null
     */
    public function updMemberInfo($request)
    {
        if (!$member = MemberBaseRepository::getOne(['id' => $request['id']])){
            $this->setError('用户不存在!');
            return false;
        }
        if ($request['end_at'] == MemberEnum::PERMANENT){
            $end_at = 0;
        }else{
            $end_at = strtotime('+' . $request['end_at'] . 'year',$member['created_at']);
        }
        $base_arr = [
            'id'         => $request['id'],
            'ch_name'    => $request['ch_name'],
            'en_name'    => $request['en_name'] ?? '',
            'avatar_id'  => $request['avatar_id'] ?? 1516,
            'sex'        => $request['sex'] ?? 0,
            'email'      => $request['email'] ?? '',
            'status'     => $request['status'] ?? MemberEnum::MEMBER,
            'hidden'     => $request['hidden'] ?? MemberEnum::ACTIVITE,
        ];
        $info_arr = [
            'member_id'      => $request['id'],
            'birthday'       => $request['birthday'] ?? 0,
            'address'        => $request['address'] ?? '' ,
            'info_provider'  => $request['info_provider'] ?? '',
            'employer'       => $request['employer'] ?? '',
            'grade'          => $request['grade'] ?? 0,
            'category'       => $request['category'] ?? 0,
            'title'          => $request['title'] ?? '',
            'industry'       => $request['industry'] ?? '',
            'position'       => $request['position'] ?? '',
            'profile'        => $request['profile'] ?? '',
        ];
        $service_arr = [
            'member_id'      => $request['id'],
            'other_server'   => $request['other_server'] ?? 1,
        ];
        $grade_arr = [
            'grade'          => $info_arr['grade'],
            'created_at'     => time(),
            'end_at'         => $end_at,
        ];
        DB::beginTransaction();
        if (!MemberBaseRepository::getUpdId(['id' => $request['id']],$base_arr)){
            DB::rollBack();
            $this->setError('信息完善失败，请重试！');
            return false;
        }
        if (!MemberInfoRepository::firstOrCreate(['member_id' => $request['id']],$info_arr)){
            DB::rollBack();
            $this->setError('信息完善失败，请重试！');
            return false;
        }
        if (!MemberPersonalServiceRepository::firstOrCreate(['member_id' => $request['id']],$service_arr)){
            DB::rollBack();
            $this->setError('信息完善失败，请重试！');
            return false;
        }
        if (!MemberGradeRepository::firstOrCreate(['user_id' => $request['id']],$grade_arr)){
            DB::rollBack();
            $this->setError('信息完善失败，请重试！');
            return false;
        }
        DB::commit();
        $this->setMessage('添加成功!');
        return true;
    }


    /**
     * 设置成员是否在首页显示
     * @param $request
     * @return bool
     */
    public function setMemberHomeDetail($request)
    {
        if (!MemberGradeViewRepository::exists(['id' => $request['id']])){
            $this->setError('成员不存在!');
            return false;
        }
        if (!MemberInfoRepository::getUpdId(['member_id' => $request['id']],['is_home_detail' => $request['exhibition']])){
            $this->setError('设置失败!');
            return false;
        }
        $this->setMessage('设置成功!');
        return true;
    }

    /**
     * 添加等级可查看等级服务
     * @param $request
     * @return bool
     */
    public function addMemberGradeView($request)
    {
        if (empty($request['type'])){
            $this->setError('类型不能为空');
            return false;
        }
        if (!isset(MemberEnum::$grade[$request['grade']]) && !isset(MemberEnum::$grade[$request['value']]) ){
            $this->setError('等级或可查看值不存在');
            return false;
        }
        $add_arr = [
            'type'      => $request['type'],
            'grade'     => $request['grade'],
            'value'     => $request['value'],
        ];
        if (OaGradeViewRepository::exists($add_arr)){
            $this->setError('查看服务已存在');
            return false;
        }
        $add_arr['created_at']  = time();
        $add_arr['updated_at']  = time();
        if (!OaGradeViewRepository::getAddId($add_arr)){
            $this->setError('添加失败!');
            return false;
        }
        $this->setMessage('添加成功!');
        return true;
    }


    /**
     * 修改等级可查看等级服务
     * @param $request
     * @return bool
     */
    public function editMemberGradeView($request)
    {
        if (empty($request['type'])){
            $this->setError('类型不能为空');
            return false;
        }
        if (!isset(MemberEnum::$grade[$request['grade']]) && !isset(MemberEnum::$grade[$request['value']]) ){
            $this->setError('等级或可查看值不存在');
            return false;
        }
        $upd_arr = [
            'type'      => $request['type'],
            'grade'     => $request['grade'],
            'value'     => $request['value'],
        ];
        if (OaGradeViewRepository::exists($upd_arr)){
            $this->setError('查看服务已存在');
            return false;
        }
        $upd_arr['updated_at']  = time();
        if (!OaGradeViewRepository::getUpdId(['id' => $request['id']],$upd_arr)){
            $this->setError('修改失败!');
            return false;
        }
        $this->setMessage('修改成功!');
        return true;
    }

    /**
     * 获取等级可查看等级服务列表
     * @param $request
     * @return array|bool|mixed|null
     */
    public function getMemberGradeViewList($request)
    {
        $page       = $request['page'] ?? 1;
        $page_num   = $request['page_num'] ?? 20;
        $where      = ['id' => ['>',0]];
        $column     = ['id','grade','type','value','created_at'];
        if (!$list = OaGradeViewRepository::getList($where,$column,'id','asc',$page,$page_num)){
            $this->setError('获取失败!');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            return [];
        }
        foreach ($list['data'] as &$value){
            $value['grade_name']  =   MemberEnum::getGrade($value['grade'],'普通成员');
            $value['value_name']  =   MemberEnum::getGrade($value['value'],'普通成员');
            $value['type_name']   =   MemberEnum::getIdentity($value['type'],'成员');
            $value['created_at']  =   date('Y-m-d H:i:s',$value['created_at']);
        }
        $this->setMessage('获取成功!');
        return $list;
    }

}