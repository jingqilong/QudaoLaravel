<?php


namespace App\Services\Oa;


use App\Enums\MemberEnum;
use App\Repositories\MemberBaseRepository;
use App\Repositories\MemberGradeRepository;
use App\Repositories\MemberGradeViewRepository;
use App\Repositories\MemberInfoRepository;
use App\Repositories\MemberPersonalServiceRepository;
use App\Repositories\OaMemberRepository;
use App\Services\BaseService;
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
    public function getMemberList(array $data)
    {
        if (empty($data['asc'])){
            $data['asc'] = 1;
        }
        $page           = $data['page'] ?? 1;
        $page_num       = $data['page_num'] ?? 20;
        $asc            = $data['asc'] ==  1 ? 'asc' : 'desc';
        $keywords       = $data['keywords'] ?? null;
        $column         = ['id','card_no','ch_name','en_name','is_recommend','sex','mobile','grade','position','address','employer','img_url','title','category','status','hidden','created_at'];
        $where          = ['deleted_at' => 0];
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
            $value['status_name']   = MemberEnum::getIdentity($value['status'],'成员');
            $value['hidden_name']   = MemberEnum::getHidden($value['hidden'],'显示');
            $value['created_at']    = date('Y-m-d H:i:s',$value['created_at']);
        }
        $this->setMessage('获取成功！');
        return $member_list;
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
        if (empty($id)){
            $this->setError('会员ID为空！');
            return false;
        }
        if (!MemberGradeViewRepository::exists(['id' => $id])){
            $this->setError('用户信息不存在!');
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
     * 成员禁用or激活 (拆表后 已修改)
     * @param $request
     * @return bool|null
     */
    public function setMemberStatus($request)
    {
        if (empty($request['id'])){
            $this->setError('会员ID为空！');
            return false;
        }
        if (!MemberEnum::isset($request['hidden'])){
            $this->setError('状态属性不存在!');
            return false;
        }
        if (!MemberGradeViewRepository::exists(['id' =>$request['id']])){
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
        $base_arr = [
            'ch_name'    => $request['ch_name'],
            'mobile'     => $request['mobile'],
            'card_no'    => $request['card_no'],
            'en_name'    => $request['en_name'] ?? '',
            'avatar_id'  => $request['avatar_id'] ?? 1226,
            'sex'        => $request['sex'] ?? 0,
            'email'      => $request['email'] ?? '',
            'status'     => $request['status'] ?? MemberEnum::MEMBER,
            'hidden'     => $request['hidden'] ?? MemberEnum::ACTIVITE,
        ];
        DB::beginTransaction();
        if (!$member_id = MemberBaseRepository::getAddId($base_arr)){
            DB::rollBack();
            $this->setError('添加失败，请重试！');
            return false;
        }
        $info_arr = [
            'member_id'      => $member_id,
            'address'        => $request['address'] ?? '' ,
            'info_provider'  => $request['info_provider'] ?? '',
            'employer'       => $request['employer'] ?? '',
            'grade'          => $request['grade'] ?? 0,
            'category'       => $request['category'] ?? 0,
            'title'          => $request['title'] ?? '',
            'industry'       => $request['industry'] ?? '',
            'position'       => $request['position'] ?? '',
        ];
        if (!MemberInfoRepository::getAddId($info_arr)){
            DB::rollBack();
            $this->setError('添加失败，请重试！');
            return false;
        }
        $service_arr = [
            'member_id'      => $member_id,
            'other_server'   => $request['other_server'] ?? 1,
        ];
        if (!MemberPersonalServiceRepository::getAddId($service_arr)){
            DB::rollBack();
            $this->setError('添加失败，请重试！');
            return false;
        }
        $grade_arr = [
            'user_id'        => $member_id,
            'grade'          => $info_arr['grade'],
            'created_at'     => time(),
            'update_at'      => time(),
            'status'         => MemberEnum::NOPASS,
            'end_at'         => strtotime('+' . $request['end_at'] . 'year'),
        ];
        if (!MemberGradeRepository::create([$grade_arr])){
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
        if (!MemberBaseRepository::getOne(['id' => $request['id']])){
            $this->setError('用户不存在!');
            return false;
        }
        if ($request['end_at'] == MemberEnum::REALLYENJOY){
            $end_at = 0;
        }else{
            $end_at = strtotime('+' . $request['end_at'] . 'year');
        }
        $base_arr = [
            'id'         => $request['id'],
            'ch_name'    => $request['ch_name'],
            'en_name'    => $request['en_name'] ?? '',
            'avatar_id'  => $request['avatar_id'] ?? 1226,
            'sex'        => $request['sex'] ?? 0,
            'email'      => $request['email'] ?? '',
            'status'     => $request['status'] ?? MemberEnum::MEMBER,
            'hidden'     => $request['hidden'] ?? MemberEnum::ACTIVITE,
        ];
        $info_arr = [
            'member_id'      => $request['id'],
            'birthday'       => $request['birthday'] ?? '',
            'address'        => $request['address'] ?? '' ,
            'info_provider'  => $request['info_provider'] ?? '',
            'employer'       => $request['employer'] ?? '',
            'grade'          => $request['grade'] ?? 0,
            'category'       => $request['category'] ?? 0,
            'title'          => $request['title'] ?? '',
            'industry'       => $request['industry'] ?? '',
            'position'       => $request['position'] ?? '',
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

}