<?php


namespace App\Services\Oa;


use App\Enums\MemberEnum;
use App\Repositories\OaMemberRepository;
use App\Services\BaseService;
use App\Traits\HelpTrait;
use Illuminate\Support\Facades\Auth;

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
     * 获取成员列表
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
        $column         = ['deleted_at','m_id','m_num','m_cname','m_ename','m_groupname','m_starte','m_workunits','m_time','m_phone','m_address','m_category','m_sex'];
        $where          = ['deleted_at' => 0];
        if (!empty($keyword)){
            $keyword        = [$keywords => ['m_cname','m_ename','m_category','m_num','m_phone','m_groupname']];
            if(!$member_list = OaMemberRepository::search($keyword,$where,$column,$page,$page_num,'m_time',$asc)){
                $this->setMessage('暂无成员信息！');
                return [];
            }
        }else {
            if(!$member_list = OaMemberRepository::getList($where,$column,'m_time',$asc,$page,$page_num)){
                $this->setMessage('没有查到该成员！');
                return [];
            }
        }

        $this->removePagingField($member_list);

        if (empty($member_list['data'])) {
            $this->setMessage('没有成员!');
        }
        foreach ($member_list['data'] as &$value){
            $value['group_name']        = empty($value['m_groupname']) ? '' : MemberEnum::getGrade($value['m_groupname']);
            $value['category_name']     = empty($value['m_category']) ? '' : MemberEnum::getCategory($value['m_category']);
            $value['starte_name']       = empty($value['m_starte']) ? '' : MemberEnum::getStatus($value['m_starte']);
            $value['sex_name']          = empty($value['m_sex']) ? '' : MemberEnum::getSex($value['m_sex']);
        }
        $this->setMessage('获取成功！');
        return $member_list;
    }

    /**
     * 获取成员信息
     * @param string $id
     * @return bool|null
     */
    public function getMemberInfo(string $id)
    {
        if (empty($id)){
            $this->setError('会员ID为空！');
            return false;
        }
        if (!$member = OaMemberRepository::exists(['m_id' => $id])){
            $this->setError('用户信息不存在!');
            return false;
        }
        if (!$memberInfo = OaMemberRepository::getOne(['m_id' => $id])){
            $this->setError('用户信息获取失败!');
            return false;
        }
        if (!$memberInfo['deleted_at']  != 0){
            $this->setError('用户已被删除，有需求请联系超级管理员!');
            return false;
        }
        if (empty($memberInfo)) {
            $this->setMessage('没有查找到此成员信息!');
        }
        $memberInfo['group_name']        = empty($memberInfo['m_groupname']) ? '' : MemberEnum::getGrade($memberInfo['m_groupname']);
        $memberInfo['category_name']     = empty($memberInfo['m_category']) ? '' : MemberEnum::getCategory($memberInfo['m_category']);
        $memberInfo['starte']            = empty($memberInfo['m_starte']) ? '' : MemberEnum::getStatus($memberInfo['m_starte']);
        $memberInfo['sex']               = empty($memberInfo['m_sex']) ? '' : MemberEnum::getSex($memberInfo['m_sex']);

        $this->setMessage('获取用户信息成功');
        return $memberInfo;
    }

    /**
     * 成员软删除
     * @param string $id
     * @return bool|null
     */
    public function delMember(string $id)
    {
        if (empty($id)){
            $this->setError('会员ID为空！');
            return false;
        }
        if (!OaMemberRepository::exists(['m_id' => $id])){
            $this->setError('用户信息不存在!');
            return false;
        }
        if (!$memberInfo = OaMemberRepository::getOne(['m_id' => $id])){
            $this->setError('没有该成员!');
            return false;
        }
        if ($memberInfo['deleted_at']  != 0){
            $this->setError('用户已被删除!');
            return false;
        }
        if (!$memberInfo = OaMemberRepository::getUpdId(['m_id' => $id],['deleted_at' => time()])){
            $this->setError('删除成员失败!');
            return false;
        }
        $this->setMessage('删除成员成功');
        return $memberInfo;
    }

    /**
     * 成员禁用or激活
     * @param string $id
     * @return bool|null
     */
    public function setMemberStatus(string $id)
    {
        if (empty($id)){
            $this->setError('会员ID为空！');
            return false;
        }
        if (!$memberID = OaMemberRepository::exists(['m_id' => $id])){
            $this->setError('用户信息不存在!');
            return false;
        }
        if (!$memberInfo = OaMemberRepository::getOne(['m_id' => $id])){
            $this->setError('用户信息获取失败!');
            return false;
        }
        if ($memberInfo['m_starte'] == MemberEnum::DELETEMEMBER){
            $this->setError('成员已被删除!');
            return false;
        }

        switch ($memberInfo['m_starte'])
        {
            case '0':
                if (!$memberStatus = OaMemberRepository::getUpdId(['m_id' => $id],['m_starte' => MemberEnum::DISABLEMEMBER])){
                    $this->setError('禁用成员失败!');
                    return false;
                }
                $this->setMessage('禁用会员成功!');
                return $memberStatus;
                break;
            case '1':
                if (!$memberStatus = OaMemberRepository::getUpdId(['m_id' => $id],['m_starte' => MemberEnum::ACTIVITEMEMBER])){
                    $this->setError('激活会员失败!');
                    return false;
                }
                $this->setMessage('激活会员成功!');
                return $memberStatus;
                break;
            case '2':
                if (!$memberStatus = OaMemberRepository::getUpdId(['m_id' => $id],['m_starte' => MemberEnum::DISABLEOFFICER])){
                    $this->setError('禁用官员失败!');
                    return false;
                }
                $this->setMessage('禁用官员成功!');
                return $memberStatus;
                break;
            case '3':
                if (!$memberStatus = OaMemberRepository::getUpdId(['m_id' => $id],['m_starte' => MemberEnum::ACTIVITEOFFICER])){
                    $this->setError('激活官员失败!');
                    return false;
                }
                $this->setMessage('激活官员成功!');
                return $memberStatus;
                break;
                default;
        }
    }


    /**
     * 添加成员
     * @param $data
     * @return bool|null
     */
    public function addMember($data)
    {
        unset($data['sign'],$data['token']);
        if (empty($data)){
            $this->setError('没有数据，请添加数据');
            return false;
        }

        if (empty($data['m_starte'])){
            $data['m_starte'] = MemberEnum::DISABLEMEMBER;
        }

        if ($old_member = OaMemberRepository::exists(['m_cname' => $data['m_cname'],'m_phone' =>$data['m_phone'],'m_ename' =>$data['m_ename'],'m_email' => $data['m_email']])){
            $this->setError('会员已存在，请不要重新添加');
            return false;
        }

        if (!$memberInfo = OaMemberRepository::getAddId($data)){
            $this->setError('添加失败，请重新添加！');
            return false;
        }

        $this->setMessage('添加成功');
        return $memberInfo;

    }


    /**
     * 更新完善成员信息
     * @param $data
     * @return bool|null
     */
    public function updMemberInfo($data)
    {
        unset($data['sign'],$data['token']);
        if (empty($data)){
            $this->setError('没有数据，请编辑修改数据');
            return false;
        }

        if (!$old_member = OaMemberRepository::getOne(['m_id' => $data['id']])){
            $this->setError('查找不到该会员,请重试！');
            return false;
        }

        $table_fields = OaMemberRepository::getFields();
        $upd_data = [];
        foreach($table_fields as $v){
            if (isset($data[$v]) && $old_member[$v] !== $data[$v]){
                $upd_data[$v] = $data[$v];
            }
        }

        if (!$updMemberInfo = OaMemberRepository::getUpdId(['m_id' => $data['id']],$upd_data)){
            $this->setError('更新失败！请重试');
            return false;
        }
        $this->setMessage('更新成功');
        return $updMemberInfo;


    }

}