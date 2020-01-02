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
     * 获取成员列表 (拆表后 已修改) （2）
     * @param array $data
     * @return mixed
     */
    public function memberList(array $data)
    {
        if (empty($data['asc'])) $data['asc'] = 1;
        $is_home_detail = $data['is_home_detail'] ?? null;
        $grade          = $data['grade'] ?? null;
        $category       = $data['category'] ?? null;
        $page           = $data['page'] ?? 1;
        $page_num       = $data['page_num'] ?? 20;
        $asc            = $data['asc'] ==  1 ? 'asc' : 'desc';
        $keywords       = $data['keywords'] ?? null;
        $where          = ['deleted_at' => 0,'is_test' => 0];
        $column = ['id','card_no','ch_name','sex','mobile','address','status','hidden','created_at','img_url',
            'end_at','is_recommend','is_home_detail','grade','title','category'];
        if (!empty($is_home_detail)) $where['is_home_detail'] = $is_home_detail;
        if (!empty($grade)) $where['grade'] = $grade;
        if (!empty($category)) $where['category'] = $category;
        if (!empty($keywords)) {
            $keyword = [$keywords => ['card_no', 'mobile', 'ch_name', 'grade', 'category']];
            if (!$list = MemberOaListViewRepository::search($keyword, $where, $column, $page, $page_num, 'id', $asc)) {
                $this->setError('获取失败!');
                return false;
            }
        }else{
            if (!$list = MemberOaListViewRepository::getList($where, $column, 'id', $asc, $page, $page_num)) {
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
     * @return mixed
     */
    public function getMemberInfo(string $id)
    {
        if (!$member_base = MemberBaseRepository::getOne(['id' => $id,'deleted_at' => 0])){
            $this->setError('用户不存在或别删除!');
            return false;
        }
        if (empty($member_info = MemberInfoRepository::getOne(['member_id' => $id]))) $member_info = [];
        if (empty($member_grade = MemberGradeRepository::getOne(['user_id' => $id])))  $member_grade = [];
        if (empty($member_service = MemberPersonalServiceRepository::getOne(['member_id' => $id]))) $member_service = [];
        $preference['preference'] = MemberPreferenceRepository::getPreference($id);
        $member_base = ImagesService::getOneImagesConcise($member_base,['avatar_id' => 'single']);
        $member = array_merge($member_base,$member_info,$member_grade,$member_service,$preference);
        $member['grade_name']    = MemberEnum::getGrade($member['grade'],'普通成员');
        $member['category_name'] = MemberEnum::getCategory($member['category'],'普通成员');
        $member['is_recommend']  = $member['is_recommend'] == 0 ? 0 : 1;
        $member['sex_name']      = MemberEnum::getSex($member['sex'],'未设置');
        $member['status_name']   = MemberEnum::getStatus($member['status'],'成员');
        $member['hidden_name']   = MemberEnum::getHidden($member['hidden'],'显示');
        $member['created_at']    = date('Y-m-d H:i:s',$member['created_at']);
        $member['birthday']      = date('Y-m-d',strtotime($member['birthday']));
        if (empty($member['birthday'])) $member['birthday'] = '';
        if (0 == $member['end_at']) $member['end_at_name'] = 0; else{
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
        if (!MemberBaseRepository::exists(['id' => $id])){
            $this->setError('用户信息不存在!');
            return false;
        }
        //检查会员是否为banner展示
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
     * 添加成员 (拆表后 已修改)
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
        if (!MemberInfoRepository::addMemberInfo($request,$member_id)){
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

    /**
     * 添加成员的基本信息
     * @param $request
     * @return mixed
     */
    public function addMemberBase($request)
    {
        $add_arr = [
            'card_no'       => $request['card_no'],
            'email'         => $request['email'] ?? '',
            'ch_name'       => $request['ch_name'],
            'en_name'       => $request['en_name'] ?? '',
            'sex'           => $request['sex'],
            'avatar_id'     => $request['avatar_id'] ?? 1516,
            'id_card'       => $request['id_card'] ?? '',
            'category'      => $request['category'],
            'birthplace'    => $request['birthplace'] ?? '',
            'status'        => $request['status'],
            'hidden'        => $request['hidden'],
            'birthday'      => $request['birthday'] ?? '',
            'zipcode'       => $request['zipcode'] ?? '',
            'address'       => $request['address'] ?? '',
            'wechat_no'     => $request['wechat_no'] ?? '',
            'zodiac'        => $request['zodiac'] ?? '',
            'constellation' => $request['constellation'] ?? '',
            'created_at'    => time(),
            'updated_at'    => time(),
        ];
        if ($request['end_at'] == MemberEnum::PERMANENT) $end_at = 0; else $end_at = strtotime('+' . $request['end_at'] . 'year',$add_arr['created_at']);
        $grade_arr = [
            'grade'       =>  $request['grade'],
            'end_at'      =>  $end_at,
            'status'      =>  MemberEnum::NOSET,
            'created_at'  =>  $add_arr['created_at'],
            'update_at'   =>  time(),
        ];
        DB::beginTransaction();
        if (!$member_id = MemberBaseRepository::addUser($request['mobile'],$add_arr)){
            $this->setError('成员添加失败!');
            DB::rollBack();
            return false;
        }
        $grade_arr['user_id'] = $member_id;
        if (!MemberGradeRepository::create([$grade_arr])){
            $this->setError('成员添加失败!');
            DB::rollBack();
            return false;
        }
        $this->setMessage('成员基本信息添加成功!');
        DB::commit();
        return ['member_id' => $member_id];
    }

    /**
     * 添加成员简历信息
     * @param $request
     * @return bool|null|mixed
     */
    public function addMemberInfo($request)
    {
        $add_arr = [
            'member_id'       => $request['member_id'],
            'employer'        => $request['employer'] ?? '',
            'position'        => $request['position'] ?? '',
            'title'           => $request['title'] ?? '',
            'industry'        => $request['industry'] ?? '',
            'brands'          => $request['brands'] ?? '',
            'run_wide'        => $request['run_wide'] ?? '',
            'profile'         => $request['profile'] ?? '',
            'goodat'          => $request['goodat'] ?? '',
            'degree'          => $request['degree'] ?? '',
            'school'          => $request['school'] ?? '',
            'remarks'         => $request['remarks'] ?? '',
            'referral_agency' => $request['referral_agency'] ?? '',
            'info_provider'   => $request['info_provider'] ?? '',
            'archive'         => $request['archive'],
            'is_home_detail'  => $request['is_home_detail'],
        ];
        if (MemberInfoRepository::exists($add_arr)){
            $this->setError('成员简历信息已存在!');
            return false;
        }
        $add_arr['is_recommend'] = $request['is_recommend'] == 1 ? time() : 0;
        $add_arr['created_at']   = time();
        $add_arr['update_at']    = time();
        if (!$member_id = MemberInfoRepository::getAddId($add_arr)){
            $this->setError('成员添加简历信息失败!');
            return false;
        }
        $this->setMessage('成员简历信息添加成功!');
        return ['member_id' => $request['member_id']];
    }

    /**
     * 添加会员服务信息 and 成员偏好类别信息
     * @param $request
     * @return mixed
     */
    public function addMemberService($request)
    {
        $service_arr = [
            'member_id'     => $request['member_id'],
            'publicity'     => $request['publicity'],
            'protocol'      => $request['protocol'],
            'nameplate'     => $request['nameplate'],
            'attendant'     => $request['attendant'] ?? '',
            'other_server'  => $request['other_server'],
            'member_attendant'  => $request['member_attendant'] ?? '',
            'gift'          => $request['gift'] ?? '',
            ];
        $preference_arr = [
            'member_id'    => $request['member_id'],
            'content'      => $request['content'] ?? ''
       ];
        if (MemberPersonalServiceRepository::exists($service_arr)){
            $this->setError('会员需求喜好类型信息已经存在');
            return false;
        }
        DB::beginTransaction();
        $service_arr['created_at'] = time();
        $service_arr['update_at']  = time();
        if (!MemberPersonalServiceRepository::getAddId($service_arr)){
            $this->setError('会员需求喜好类型信息添加失败!');
            DB::rollBack();
            return false;
        }
        if (MemberPreferenceRepository::exists(['member_id' => $request['member_id']])) if (!MemberPreferenceRepository::delete($request['member_id'])){
            $this->setError('会员需求喜好类型信息添加失败!');
            DB::rollBack();
            return false;
        }
        $preference_arr['created_at'] = time();
        $preference_arr['update_at']  = time();
        if (!MemberPreferenceRepository::getAddId($preference_arr)){
            $this->setError('会员需求喜好类型信息添加失败!');
            DB::rollBack();
            return false;
        }
        $this->setMessage('添加成功!');
        DB::commit();
        return ['member_id' => $request['member_id']];
    }

    /**
     * 编辑成员风采展示信息
     * @param $request
     * @return mixed
     */
    public function editMemberBase($request)
    {
        $request['updated_at'] = time();
        if (!$member_base = MemberBaseRepository::getOne(['id' => $request['id']])){
            $this->setError('没有该会员信息!');
            return false;
        }
        $base_upd  = [];
        $grade_upd = [];
        $member_grade        = MemberGradeRepository::getOne(['user_id' => $member_base['id']]);
        $member_base_fields  = MemberBaseRepository::getFields();
        $member_grade_fields = MemberGradeRepository::getFields();
        if ($request['end_at'] == MemberEnum::PERMANENT) $request['end_at'] = 0; else $request['end_at'] = strtotime($request['end_at']);
        foreach($member_base_fields as $v){
            if (isset($request[$v]) && $member_base[$v] !== $request[$v]){
                $base_upd[$v] = $request[$v];
            }
        }
        foreach($member_grade_fields as $value){
            if (isset($request[$value]) && $member_grade[$value] !== $request[$value]){
                $grade_upd[$value] = $request[$value];
            }
        }
        DB::beginTransaction();
        if (!empty($base_upd)){
            if (!MemberBaseRepository::getUpdId(['id' => $request['id']],$base_upd)){
                $this->setError('成员修改失败!');
                DB::rollBack();
                return false;
            }
        }
        if (!empty($grade_upd)) {
            if (!MemberGradeRepository::getUpdId(['user_id' => $member_base['id']],$grade_upd)) {
                $this->setError('成员修改失败!');
                DB::rollBack();
                return false;
            }
        }
        $this->setMessage('成员基本信息修改成功!');
        DB::commit();
        return $member_base['id'];
    }

    /**
     * 修改成员简历信息
     * @param $request
     * @return mixed
     */
    public function editMemberInfo($request)
    {
        $request['updated_at'] = time();
        if (!$member_info = MemberInfoRepository::getOne(['member_id' => $request['member_id']])){
            if (!$this->addMemberInfo($request)){
                $this->setError('添加成员风采展示信息失败!');
            }
            $this->setMessage('添加成员风采展示信息成功!');
            return $request['member_id'];
        }
        $info_upd = [];
        $member_info_fields = MemberInfoRepository::getFields();
        foreach($member_info_fields as $v){
            if (isset($request[$v]) && $member_info[$v] !== $request[$v]){
                $info_upd[$v] = $request[$v];
            }
        }
        if (!empty($info_upd)){
            if (!MemberInfoRepository::getUpdId(['member_id' => $member_info['member_id']],$info_upd)){
                $this->setError('成员简历信息修改失败!');
                return false;
            }
        }
        $this->setMessage('成员简历信息修改成功!');
        return ['member_id' => $member_info['member_id']];
    }

    /**
     * 编辑会员喜好需求信息展示
     * @param $request
     * @return bool
     */
    public function editMemberService($request)
    {
        $request['updated_at'] = time();
        if (!$member_service = MemberPersonalServiceRepository::getOne(['member_id' => $request['member_id']])){
            if (!$this->addMemberService($request)){
                $this->setError('会员需求喜好类型信息添加失败!');
                return false;
            }
            $this->setMessage('会员需求喜好类型信息添加成功!');
            return $request['member_id'];
        }
        $member_preference        = MemberPreferenceRepository::getOne(['member_id' => $member_service['id']]);
        $member_service_fields    = MemberPersonalServiceRepository::getFields();
        $member_preference_fields = MemberPreferenceRepository::getFields();
        $service_upd    = [];
        $preference_upd = [];
        foreach($member_service_fields as $v){
            if (isset($request[$v]) && $member_service[$v] !== $request[$v]){
                $service_upd[$v] = $request[$v];
            }
        }
        foreach($member_preference_fields as $value){
            if (isset($request[$value]) && $member_preference[$value] !== $request[$value]){
                $preference_upd[$value] = $request[$value];
            }
        }
        DB::beginTransaction();
        if (!empty($service_upd)){
            if (!MemberPersonalServiceRepository::getUpdId(['member_id' => $request['member_id']],$service_upd)){
                $this->setError('成员修改失败!');
                DB::rollBack();
                return false;
            }
        }
        if (!empty($preference_upd)) {
            if (!MemberPreferenceRepository::getUpdId(['member_id' => $request['member_id']],$preference_upd)) {
                $this->setError('成员修改失败!');
                DB::rollBack();
                return false;
            }
        }
        $this->setMessage('成员基本信息修改成功!');
        DB::commit();
        return $request['member_id'];
    }

}