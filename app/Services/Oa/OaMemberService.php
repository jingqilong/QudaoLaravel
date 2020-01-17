<?php


namespace App\Services\Oa;


use App\Enums\CommonHomeEnum;
use App\Enums\MemberEnum;
use App\Repositories\MemberBaseRepository;
use App\Repositories\MemberGradeDefineRepository;
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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OaMemberService extends BaseService
{
    use HelpTrait;
    protected $auth;

    /**
     * 排序默认变量
     * @var int
     */
    protected $sort = 2;

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
    public function memberList(array $data)
    {
        if (empty($data['asc'])) $data['asc'] = $this->sort;
        $asc        = $data['asc'] == 1 ? 'asc' : 'desc';
        $where      = ['deleted_at' => 0, 'is_test' => 0];
        $keywords   = $data['keywords'] ?? null;
        $column     = ['id', 'card_no', 'ch_name', 'sex', 'mobile','grade', 'img_url', 'address', 'is_home_detail', 'status', 'hidden', 'created_at', 'category'];
        $where_arr  = Arr::only($data, ['is_home_detail', 'grade', 'category', 'sex']);
        foreach ($where_arr as $key => $value) {
            if (!is_null($value)) {
                $where[$key] = $value;
            }
        }
        if (!empty($keywords)) {
            $keyword = [$keywords => ['card_no', 'ch_name', 'mobile']];
            if (!$list = MemberOaListViewRepository::search($keyword, $where, $column, 'id', $asc)) {
                $this->setError('获取失败!');
                return false;
            }
        } else {
            if (!$list = MemberOaListViewRepository::getList($where, $column, 'id', $asc)) {
                $this->setError('获取失败!');
                return false;
            }
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])) {
            $this->setMessage('获取成功!');
            return [];
        }
        foreach ($list['data'] as &$value) {
            if (empty($value['grade'])) $value['grade'] = 0;
            $value['category_name'] = MemberEnum::getCategory($value['category'], '普通成员');
            $value['grade_name']    = MemberGradeDefineRepository::getLabelById($value['grade'], '普通成员');
            $value['sex_name']      = MemberEnum::getSex($value['sex'], '未设置');
            $value['status_name']   = MemberEnum::getStatus($value['status'], '成员');
            $value['hidden_name']   = MemberEnum::getHidden($value['hidden'], '显示');
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
        if (!$member_info = MemberInfoRepository::getOne(['member_id' => $id])) $member_info = ['is_recommend' => 0];
        if (!$member_grade = MemberGradeRepository::getOne(['user_id' => $id]))  $member_grade = ['grade' => 0,'end_at' => 0];
        if (!$member_service = MemberPersonalServiceRepository::getOne(['member_id' => $id])) $member_service = [];
        $preference['content'] = MemberPreferenceRepository::getPreference($id);
        $member_base = ImagesService::getOneImagesConcise($member_base,['avatar_id' => 'single']);
        $base    = array_merge($member_base,$member_grade);
        $service = array_merge($preference,$member_service);
        $member  = ['base' => $base,'info' => $member_info,'service' => $service];
        $member['base']['grade_name']    = MemberGradeDefineRepository::getLabelById($member['base']['grade'],'普通成员');
        $member['base']['category_name'] = MemberEnum::getCategory($member['base']['category'],'普通成员');
        $member['info']['is_recommend']  = $member['info']['is_recommend'] == 0 ? 0 : 1;
        $member['base']['sex_name']      = MemberEnum::getSex($member['base']['sex'],'未设置');
        $member['base']['status_name']   = MemberEnum::getStatus($member['base']['status'],'成员');
        $member['base']['hidden_name']   = MemberEnum::getHidden($member['base']['hidden'],'显示');
        if (empty($member['base']['birthday'])) $member['base']['birthday'] = ''; else
            $member['base']['birthday']  = date('Y-m-d',strtotime($member['base']['birthday']));
        if (0 == $member['base']['end_at']) $member['base']['end_at'] = 0; else{
            $member['base']['end_at']    = date('Y-m-d H:i:s',$member['base']['end_at']);
        }
        unset($member['base']['created_at'],$member['info']['created_at'],$member['service']['created_at'],
              $member['base']['update_at'],$member['base']['updated_at'],$member['info']['update_at'],$member['service']['update_at']);
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
     * 设置成员是否在首页显示
     * @param $request
     * @return bool
     */
    public function setMemberHomeDetail($request)
    {
        $add_arr = ['is_home_detail' => $request['exhibition'],'created_at' => time(),'update_at' => time()];
        if (!MemberInfoRepository::updateOrInsert(['member_id' => $request['id']],$add_arr)){
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

        if ((!MemberGradeDefineRepository::hasID($request['grade'])) && (!MemberGradeDefineRepository::hasID($request['value'])) ){
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
        if (!MemberGradeDefineRepository::hasID($request['grade']) && !MemberGradeDefineRepository::hasID($request['value']) ){
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
        $where      = ['id' => ['>',0]];
        $column     = ['id','grade','type','value','created_at'];
        if (!$list = OaGradeViewRepository::getList($where,$column,'id','asc')){
            $this->setError('获取失败!');
            return false;
        }
        $list = $this->removePagingField($list);
        if (empty($list['data'])){
            return [];
        }
        foreach ($list['data'] as &$value){
            $value['grade_name']  =   MemberGradeDefineRepository::getLabelById($value['grade'],'普通成员');
            $value['value_name']  =   MemberGradeDefineRepository::getLabelById($value['value'],'普通成员');
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
        $add_arr = Arr::only($request,['card_no','mobile','email','ch_name','en_name','sex','avatar_id','id_card','birthplace','birthday','category','wechat_no','zipcode','address','zodiac','constellation','status','hidden']);
        $add_arr['created_at'] = time();
        $add_arr['updated_at'] = time();
        if ($request['end_at'] == MemberEnum::PERMANENT) $end_at = 0; else $end_at = strtotime('+' . $request['end_at'] . 'year',$add_arr['created_at']);
        $grade_arr = [
            'grade'       =>  $request['grade'],
            'end_at'      =>  $end_at,
            'status'      =>  MemberEnum::NOSET,
            'created_at'  =>  $add_arr['created_at'],
            'update_at'   =>  time(),
        ];
        DB::beginTransaction();
        if (!$member_id = MemberBaseRepository::addUser($add_arr['mobile'],$add_arr)){
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
        $add_arr = Arr::only($request,['employer','position','is_recommend','is_home_detail','title','industry','brands','run_wide','profile','good_at','degree','school','constellation','remarks','referral_agency','info_provider','archive']);
        $add_arr['is_recommend'] = $request['is_recommend'] == 0 ? 0 : time();
        $add_arr['created_at']   = time();
        $add_arr['update_at']    = time();
        if (!$member_id = MemberInfoRepository::updateOrInsert(['member_id' => $request['member_id']],$add_arr)){
            $this->setError('成员添加简历信息失败!');
            return false;
        }
        $this->setMessage('成员简历信息添加成功!');
        return ['member_id' => $request['member_id']];
    }

    /**
     * 添加会员服务信息 and 成员喜好需求信息
     * @param $request
     * @return mixed
     */
    public function addMemberService($request)
    {
        $request['created_at'] = time();
        $request['update_at']  = time();
        $service_arr    = Arr::only($request,['publicity','protocol','nameplate','attendant','other_server','member_attendant','gift','created_at','update_at']);
        $preference_arr = Arr::only($request,['content','created_at','update_at']);
        DB::beginTransaction();
        if (!MemberPersonalServiceRepository::updateOrInsert(['member_id' => $request['member_id']],$service_arr)){
            $this->setError('会员需求喜好类型信息添加失败!');
            DB::rollBack();
            return false;
        }
        if (!MemberPreferenceRepository::updateOrInsert(['member_id' => $request['member_id']],$preference_arr)){
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
        $member_base_fields  = MemberBaseRepository::getFields();
        if ($request['end_at'] == MemberEnum::PERMANENT) $request['end_at'] = 0; else $request['end_at'] = strtotime($request['end_at']);
        foreach($member_base_fields as $field){
            if (isset($request[$field]) && $member_base[$field] !== $request[$field]){
                $base_upd[$field] = $request[$field];
            }
        }
        if (isset($base_upd['id_card']) && MemberBaseRepository::exists(['id_card' => $base_upd['id_card']])){
            $this->setError('身份证号已存在!');
            return false;
        }
        $grade_upd = [
            'grade'      => $request['grade'] ?? MemberGradeDefineRepository::DEFAULT(),
            'status'     => MemberEnum::PASS,
            'end_at'     => $request['end_at'],
            'update_at'  => time(),
        ];
        DB::beginTransaction();
        if (!empty($base_upd)){
            if (!MemberBaseRepository::getUpdId(['id' => $request['id']],$base_upd)){
                $this->setError('成员修改失败!');
                DB::rollBack();
                return false;
            }
        }
        if (!empty($grade_upd)) {
            if (!MemberGradeRepository::updateOrInsert(['user_id' => $request['id']],$grade_upd)) {
                $this->setError('成员修改失败!');
                DB::rollBack();
                return false;
            }
        }
        $this->setMessage('成员基本信息修改成功!');
        DB::commit();
        return ['member_id' => $request['id']];
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
            if (!MemberInfoRepository::updateOrInsert(['member_id' => $member_info['member_id']],$info_upd)){
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
     * @return mixed
     */
    public function editMemberService($request)
    {
        $request['updated_at'] = time();
        $member_service_upd    = Arr::only($request,['publicity','other_server','protocol','nameplate','attendant','member_attendant','gift']);
        $member_preference_upd = Arr::only($request,['type','content']);
        DB::beginTransaction();
        if (!empty($member_service_upd)){
            if (!MemberPersonalServiceRepository::updateOrInsert(['member_id' => $request['member_id']],$member_service_upd)){
                $this->setError('成员修改失败!');
                DB::rollBack();
                return false;
            }
        }
        if (!empty($member_preference_upd)) {
            if (!MemberPreferenceRepository::updateOrInsert(['member_id' => $request['member_id']],$member_preference_upd)) {
                $this->setError('成员信息修改失败!');
                DB::rollBack();
                return false;
            }
        }
        $this->setMessage('成员信息修改成功!');
        DB::commit();
        return ['member_id' => $request['member_id']];
    }


}